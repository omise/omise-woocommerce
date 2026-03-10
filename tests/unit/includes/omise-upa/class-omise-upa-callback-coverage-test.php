<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

if ( ! class_exists( 'Omise_UPA_Payment_Resolver' ) ) {
	class Omise_UPA_Payment_Resolver {
		const STATE_SUCCESSFUL = 'successful';
		const STATE_FAILED     = 'failed';
		const STATE_PENDING    = 'pending';

		public static $responses = array();
		public static $exception = null;

		public function resolve( $order ) {
			if ( self::$exception instanceof Exception ) {
				throw self::$exception;
			}

			if ( ! empty( self::$responses ) ) {
				return array_shift( self::$responses );
			}

			return array( 'state' => self::STATE_PENDING );
		}
	}
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_UPA_Callback_Coverage_Test extends Omise_Test_Case {
	public static $wc_instance = null;
	public static $omise_instance = null;

	protected function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'WC' ) ) {
			function WC() {
				return Omise_UPA_Callback_Coverage_Test::$wc_instance;
			}
		}

		if ( ! function_exists( 'Omise' ) ) {
			function Omise() {
				return Omise_UPA_Callback_Coverage_Test::$omise_instance;
			}
		}

		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-session-service.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-state-token.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-callback.php';

		Omise_UPA_Payment_Resolver::$responses = array();
		Omise_UPA_Payment_Resolver::$exception = null;
		self::$wc_instance                     = null;
		self::$omise_instance                  = new class {
			public function translate( $message ) {
				return 'translated: ' . $message;
			}
		};

		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return $value;
				},
				'wp_unslash' => function( $value ) {
					return $value;
				},
				'wp_kses' => function( $value ) {
					return $value;
				},
				'esc_html' => function( $value ) {
					return $value;
				},
				'absint' => function( $value ) {
					return abs( (int) $value );
				},
				'apply_filters' => function( $filter, $value, ...$args ) {
					return $value;
				},
			)
		);
	}

	protected function tearDown(): void {
		$_GET = array();
		Omise_UPA_Payment_Resolver::$responses = array();
		Omise_UPA_Payment_Resolver::$exception = null;
		self::$wc_instance = null;
		self::$omise_instance = null;

		parent::tearDown();
	}

	public function test_complete_processes_successful_result_and_redirects_to_thank_you() {
		$_GET = array(
			'order_id'        => '44',
			'omise_upa_state' => 'state_123',
		);

		Omise_UPA_Payment_Resolver::$responses = array(
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_SUCCESSFUL,
				'charge' => array(
					'id' => 'chrg_success_123',
				),
			),
		);

		$order_received_url = 'https://shop.test/checkout/order-received/44/?key=wc_order_abc';
		$guarded_url        = $order_received_url . '&omise_upa_guard=1';

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'state_123' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_transaction_id' )->once()->andReturn( '' );
		$order->shouldReceive( 'set_transaction_id' )->once()->with( 'chrg_success_123' );
		$order->shouldReceive( 'payment_complete' )->once();
		$order->shouldReceive( 'add_order_note' )
			->once()
			->with( Mockery::on( function( $note ) {
				return false !== strpos( $note, 'Omise UPA: Payment successful.' );
			} ) );
		$order->shouldReceive( 'get_total' )->once()->andReturn( '1000.00' );
		$order->shouldReceive( 'get_currency' )->once()->andReturn( 'THB' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( 'is_omise_payment_resolved', 'yes' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( Omise_UPA_Callback::META_RETRY_ATTEMPTS );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( Omise_UPA_Session_Service::META_STATE );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'get_checkout_order_received_url' )->once()->andReturn( $order_received_url );

		$cart = Mockery::mock( 'WC_Cart' );
		$cart->shouldReceive( 'empty_cart' )->once();

		$wc = Mockery::mock( 'WooCommerce' );
		$wc->cart = $cart;
		self::$wc_instance = $wc;

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );
		Monkey\Functions\expect( 'add_query_arg' )
			->once()
			->with( 'omise_upa_guard', '1', $order_received_url )
			->andReturn( $guarded_url );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( $guarded_url )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::complete();
	}

	public function test_complete_processes_pending_result_and_schedules_recheck() {
		$_GET = array(
			'order_id'        => '44',
			'omise_upa_state' => 'state_123',
		);

		Omise_UPA_Payment_Resolver::$responses = array(
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_PENDING,
				'charge' => array(
					'id' => 'chrg_pending_123',
				),
				'payment' => array(
					'status' => 'pending',
				),
			),
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_PENDING,
				'charge' => array(
					'id' => 'chrg_pending_123',
				),
				'payment' => array(
					'status' => 'pending',
				),
			),
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_PENDING,
				'charge' => array(
					'id' => 'chrg_pending_123',
				),
				'payment' => array(
					'status' => 'pending',
				),
			),
		);

		$order_received_url = 'https://shop.test/checkout/order-received/44/?key=wc_order_abc';

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'state_123' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_transaction_id' )->once()->andReturn( '' );
		$order->shouldReceive( 'set_transaction_id' )->once()->with( 'chrg_pending_123' );
		$order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise UPA: Payment is pending. We are waiting for final confirmation from the payment provider.' );
		$order->shouldReceive( 'update_status' )->once()->with( 'on-hold' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( 'is_omise_payment_resolved', 'no' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( Omise_UPA_Session_Service::META_RESOLVED, 'no' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'get_id' )->once()->andReturn( 44 );
		$order->shouldReceive( 'get_checkout_order_received_url' )->once()->andReturn( $order_received_url );

		$queue = Mockery::mock();
		$queue->shouldReceive( 'schedule_single' )
			->once()
			->with(
				Mockery::on( function( $run_at ) {
					return is_int( $run_at ) && $run_at >= time();
				} ),
				Omise_UPA_Callback::PENDING_RECHECK_ACTION,
				array(
					'order_id' => 44,
					'attempt' => 1,
				),
				Omise_UPA_Callback::PENDING_RECHECK_GROUP
			);

		$wc = Mockery::mock( 'WooCommerce' );
		$wc->cart = null;
		$wc->shouldReceive( 'queue' )->andReturn( $queue );
		self::$wc_instance = $wc;

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( $order_received_url )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::complete();
	}

	public function test_complete_processes_non_retryable_failed_result_and_redirects_to_checkout() {
		$_GET = array(
			'order_id'        => '44',
			'omise_upa_state' => 'state_123',
		);

		Omise_UPA_Payment_Resolver::$responses = array(
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_FAILED,
				'charge' => array(
					'id' => 'chrg_failed_123',
					'status' => 'failed',
					'failure_message' => 'insufficient funds',
					'failure_code' => 'insufficient_fund',
				),
			),
		);

		$order_note_helper = Mockery::mock( 'alias:Omise_WC_Order_Note' );
		$order_note_helper->shouldReceive( 'get_payment_failed_note' )
			->once()
			->andReturn( 'failed note' );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'state_123' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_transaction_id' )->once()->andReturn( '' );
		$order->shouldReceive( 'set_transaction_id' )->once()->with( 'chrg_failed_123' );
		$order->shouldReceive( 'add_order_note' )->once()->with( 'failed note' );
		$order->shouldReceive( 'update_status' )->once()->with( 'failed' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( 'is_omise_payment_resolved', 'yes' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( Omise_UPA_Session_Service::META_STATE );
		$order->shouldReceive( 'save' )->once();

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );
		Monkey\Functions\expect( 'wc_add_notice' )
			->once()
			->with( Mockery::on( function( $notice ) {
				return false !== strpos( $notice, 'translated: insufficient funds (code: insufficient_fund)' );
			} ), 'error' );
		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( 'https://shop.test/checkout/' )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::complete();
	}

	public function test_handle_failed_payment_marks_retryable_failure_without_redirect() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'handle_failed_payment' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_transaction_id' )->once()->andReturn( '' );
		$order->shouldReceive( 'set_transaction_id' )->once()->with( 'chrg_retry_123' );
		$order->shouldReceive( 'get_meta' )
			->twice()
			->with( Omise_UPA_Callback::META_RETRY_ATTEMPTS )
			->andReturn( '0' );
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'update_meta_data' )->once()->with( Omise_UPA_Callback::META_RETRY_ATTEMPTS, '1' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( 'is_omise_payment_resolved', 'no' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( Omise_UPA_Session_Service::META_RESOLVED, 'no' );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( Omise_UPA_Session_Service::META_STATE );
		$order->shouldReceive( 'update_status' )->once()->with( 'pending' );
		$order->shouldReceive( 'save' )->once();

		$result = array(
			'charge' => array(
				'id' => 'chrg_retry_123',
				'status' => 'failed',
				'failure_message' => 'temporary issue',
				'failure_code' => 'processing_error',
			),
		);

		$method->invoke( null, $order, $result, false );
		$this->assertTrue( true );
	}

	public function test_complete_handles_resolver_exception_and_redirects_to_checkout() {
		$_GET = array(
			'order_id'        => '44',
			'omise_upa_state' => 'state_123',
		);

		Omise_UPA_Payment_Resolver::$exception = new Exception( 'resolver unavailable' );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'state_123' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'add_order_note' )
			->once()
			->with( Mockery::on( function( $note ) {
				return false !== strpos( $note, 'resolver unavailable' );
			} ) );

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );
		Monkey\Functions\expect( 'wc_add_notice' )
			->once()
			->with( 'Unable to validate payment status. Please check your order later.', 'error' );
		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( 'https://shop.test/checkout/' )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::complete();
	}

	public function test_complete_redirects_to_checkout_when_callback_state_is_invalid() {
		$_GET = array(
			'order_id'        => '44',
			'omise_upa_state' => 'state_123',
		);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->twice()
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'different_state' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'add_order_note' )->once()->with( 'Omise UPA: Invalid callback state.' );

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );
		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( 'https://shop.test/checkout/' )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::complete();
	}

	public function test_recheck_pending_order_schedules_next_attempt_after_exception_before_limit() {
		Omise_UPA_Payment_Resolver::$exception = new Exception( 'temporary timeout' );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'has_status' )
			->once()
			->with( array( 'failed', 'cancelled', 'refunded' ) )
			->andReturn( false );
		$order->shouldReceive( 'get_id' )->once()->andReturn( 44 );

		$queue = Mockery::mock();
		$queue->shouldReceive( 'schedule_single' )
			->once()
			->with(
				Mockery::type( 'int' ),
				Omise_UPA_Callback::PENDING_RECHECK_ACTION,
				array(
					'order_id' => 44,
					'attempt' => 2,
				),
				Omise_UPA_Callback::PENDING_RECHECK_GROUP
			);

		$wc = Mockery::mock( 'WooCommerce' );
		$wc->cart = null;
		$wc->shouldReceive( 'queue' )->andReturn( $queue );
		self::$wc_instance = $wc;

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 44, 1 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_logs_error_when_exception_reaches_attempt_limit() {
		Omise_UPA_Payment_Resolver::$exception = new Exception( 'service unavailable' );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'has_status' )
			->once()
			->with( array( 'failed', 'cancelled', 'refunded' ) )
			->andReturn( false );
		$order->shouldReceive( 'add_order_note' )
			->once()
			->with( Mockery::on( function( $note ) {
				return false !== strpos( $note, 'Automatic status check failed after 96 attempts' );
			} ) );
		$order->shouldReceive( 'save' )->once();

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 44, 96 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_marks_successful_state_without_redirect() {
		Omise_UPA_Payment_Resolver::$responses = array(
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_SUCCESSFUL,
				'charge' => array( 'id' => 'chrg_success_999' ),
			),
		);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'has_status' )
			->once()
			->with( array( 'failed', 'cancelled', 'refunded' ) )
			->andReturn( false );
		$order->shouldReceive( 'get_transaction_id' )->once()->andReturn( '' );
		$order->shouldReceive( 'set_transaction_id' )->once()->with( 'chrg_success_999' );
		$order->shouldReceive( 'payment_complete' )->once();
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'get_total' )->once()->andReturn( '1000.00' );
		$order->shouldReceive( 'get_currency' )->once()->andReturn( 'THB' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( 'is_omise_payment_resolved', 'yes' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( Omise_UPA_Callback::META_RETRY_ATTEMPTS );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( Omise_UPA_Session_Service::META_STATE );
		$order->shouldReceive( 'save' )->once();

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 44, 1 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_marks_failed_state_without_redirect() {
		Omise_UPA_Payment_Resolver::$responses = array(
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_FAILED,
				'charge' => array(
					'id' => 'chrg_failed_999',
					'status' => 'failed',
					'failure_message' => 'bank unavailable',
					'failure_code' => 'insufficient_fund',
				),
			),
		);

		$order_note_helper = Mockery::mock( 'alias:Omise_WC_Order_Note' );
		$order_note_helper->shouldReceive( 'get_payment_failed_note' )
			->once()
			->andReturn( 'failed note' );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'has_status' )
			->once()
			->with( array( 'failed', 'cancelled', 'refunded' ) )
			->andReturn( false );
		$order->shouldReceive( 'get_transaction_id' )->once()->andReturn( '' );
		$order->shouldReceive( 'set_transaction_id' )->once()->with( 'chrg_failed_999' );
		$order->shouldReceive( 'add_order_note' )->once()->with( 'failed note' );
		$order->shouldReceive( 'update_status' )->once()->with( 'failed' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( 'is_omise_payment_resolved', 'yes' );
		$order->shouldReceive( 'update_meta_data' )->once()->with( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( Omise_UPA_Session_Service::META_STATE );
		$order->shouldReceive( 'save' )->once();

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 44, 1 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_adds_note_when_pending_after_attempt_limit() {
		Omise_UPA_Payment_Resolver::$responses = array(
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_PENDING,
			),
		);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'has_status' )
			->once()
			->with( array( 'failed', 'cancelled', 'refunded' ) )
			->andReturn( false );
		$order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise UPA: Payment is still pending after automatic checks. Please use Sync Payment Status later.' );
		$order->shouldReceive( 'save' )->once();

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 44, 96 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_schedules_next_attempt_when_still_pending_under_limit() {
		Omise_UPA_Payment_Resolver::$responses = array(
			array(
				'state' => Omise_UPA_Payment_Resolver::STATE_PENDING,
			),
		);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->once()->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'has_status' )
			->once()
			->with( array( 'failed', 'cancelled', 'refunded' ) )
			->andReturn( false );
		$order->shouldReceive( 'get_id' )->once()->andReturn( 44 );

		$queue = Mockery::mock();
		$queue->shouldReceive( 'schedule_single' )
			->once()
			->with(
				Mockery::type( 'int' ),
				Omise_UPA_Callback::PENDING_RECHECK_ACTION,
				array(
					'order_id' => 44,
					'attempt' => 2,
				),
				Omise_UPA_Callback::PENDING_RECHECK_GROUP
			);

		$wc = Mockery::mock( 'WooCommerce' );
		$wc->cart = null;
		$wc->shouldReceive( 'queue' )->andReturn( $queue );
		self::$wc_instance = $wc;

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 44, 1 );
		$this->assertTrue( true );
	}

	public function test_redirect_to_order_pay_falls_back_to_checkout_when_order_pay_url_is_empty() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'redirect_to_order_pay' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_checkout_payment_url' )->once()->with( true )->andReturn( '' );

		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( 'https://shop.test/checkout/' )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		$method->invoke( null, $order );
	}
}
