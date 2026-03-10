<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_UPA_Callback_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-session-service.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-state-token.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-callback.php';
	}

	protected function tearDown(): void {
		$_GET = array();
		parent::tearDown();
	}

	public function test_cancel_updates_order_status_to_cancelled() {
		$_GET = array(
			'order_id'        => '44',
			'omise_upa_state' => 'state_123',
		);

		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return $value;
				},
				'wp_unslash'          => function( $value ) {
					return $value;
				},
			)
		);
		Monkey\Functions\expect( 'wc_get_checkout_url' )
			->once()
			->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'wc_add_notice' )
			->once()
			->with( 'Payment was cancelled. Please try again.', 'error' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( 'https://shop.test/checkout/' )
			->andThrow( new Exception( 'redirected' ) );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'state_123' );
		$order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise UPA: Payment was cancelled by customer.' );
		$order->shouldReceive( 'update_status' )
			->once()
			->with( 'cancelled' );
		$order->shouldReceive( 'update_meta_data' )
			->once()
			->with( 'is_omise_payment_resolved', 'yes' );
		$order->shouldReceive( 'update_meta_data' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		$order->shouldReceive( 'delete_meta_data' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE );
		$order->shouldReceive( 'save' )->once();

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( '44' )
			->andReturn( $order );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::cancel();
	}

	public function test_complete_adds_back_navigation_guard_for_paid_orders() {
		$_GET = array(
			'order_id'        => '44',
			'omise_upa_state' => 'state_123?provider=1',
		);

		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return $value;
				},
				'wp_unslash'          => function( $value ) {
					return $value;
				},
			)
		);

		$order_received_url = 'https://shop.test/checkout/order-received/44/?key=wc_order_abc';
		$guarded_url        = $order_received_url . '&omise_upa_guard=1';

		Monkey\Functions\expect( 'add_query_arg' )
			->once()
			->with( 'omise_upa_guard', '1', $order_received_url )
			->andReturn( $guarded_url );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( $guarded_url )
			->andThrow( new Exception( 'redirected' ) );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'state_123' );
		$order->shouldReceive( 'is_paid' )
			->once()
			->andReturn( true );
		$order->shouldReceive( 'update_meta_data' )
			->once()
			->with( 'is_omise_payment_resolved', 'yes' );
		$order->shouldReceive( 'update_meta_data' )
			->once()
			->with( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		$order->shouldReceive( 'delete_meta_data' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'get_checkout_order_received_url' )
			->once()
			->andReturn( $order_received_url );

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( '44' )
			->andReturn( $order );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::complete();
	}

	public function test_resolve_failed_order_status_maps_expired_to_cancelled() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'resolve_failed_order_status' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$cancelled_status = $method->invoke( null, array( 'charge' => array( 'status' => 'expired' ) ) );
		$failed_status    = $method->invoke( null, array( 'charge' => array( 'status' => 'failed' ) ) );

		$this->assertSame( 'cancelled', $cancelled_status );
		$this->assertSame( 'failed', $failed_status );
	}

	// ─── resolve_failed_order_status additional cases ────────────────────

	public function test_resolve_failed_order_status_maps_reversed_to_cancelled() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'resolve_failed_order_status' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$this->assertSame( 'cancelled', $method->invoke( null, array( 'charge' => array( 'status' => 'reversed' ) ) ) );
		$this->assertSame( 'cancelled', $method->invoke( null, array( 'charge' => array( 'status' => 'cancelled' ) ) ) );
		$this->assertSame( 'cancelled', $method->invoke( null, array( 'charge' => array( 'status' => 'canceled' ) ) ) );
	}

	public function test_resolve_failed_order_status_returns_failed_when_no_charge() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'resolve_failed_order_status' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$this->assertSame( 'failed', $method->invoke( null, array() ) );
		$this->assertSame( 'failed', $method->invoke( null, array( 'charge' => null ) ) );
	}

	// ─── get_order_from_request Tests ────────────────────────────────────

	public function test_get_order_from_request_returns_null_when_no_order_id() {
		$_GET = array();

		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_order_from_request' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
		) );

		$this->assertNull( $method->invoke( null ) );
	}

	public function test_get_order_from_request_returns_null_for_non_numeric_id() {
		$_GET = array( 'order_id' => 'abc' );

		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_order_from_request' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
		) );

		$this->assertNull( $method->invoke( null ) );
	}

	public function test_get_order_from_request_returns_order_for_valid_id() {
		$_GET = array( 'order_id' => '55' );

		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_order_from_request' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
		) );

		$order = Mockery::mock( 'WC_Order' );
		Monkey\Functions\expect( 'wc_get_order' )->once()->with( 55 )->andReturn( $order );

		$this->assertSame( $order, $method->invoke( null ) );
	}

	// ─── get_state_from_request Tests ────────────────────────────────────

	public function test_get_state_from_request_returns_null_when_not_set() {
		$_GET = array();

		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_state_from_request' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$this->assertNull( $method->invoke( null ) );
	}

	public function test_get_state_from_request_strips_provider_params() {
		$_GET = array( 'omise_upa_state' => 'state_abc?provider=paypal&extra=1' );

		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_state_from_request' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
		) );

		$this->assertSame( 'state_abc', $method->invoke( null ) );
	}

	public function test_get_state_from_request_returns_null_for_empty_state() {
		$_GET = array( 'omise_upa_state' => '  ' );

		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_state_from_request' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
		) );

		$this->assertNull( $method->invoke( null ) );
	}

	// ─── is_retryable_failure Tests ──────────────────────────────────────

	public function test_is_retryable_failure_returns_true_for_retryable_codes() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'is_retryable_failure' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'apply_filters' )->andReturnUsing(
			function( $filter, $default ) { return $default; }
		);

		$retryable_codes = array( 'processing_error', 'temporarily_unavailable', 'provider_unavailable', 'provider_timeout', 'timeout', 'network_error', 'internal_error' );

		foreach ( $retryable_codes as $code ) {
			$result = array( 'charge' => array( 'failure_code' => $code ) );
			$this->assertTrue( $method->invoke( null, $result ), "Expected $code to be retryable" );
		}
	}

	public function test_is_retryable_failure_returns_false_for_non_retryable_code() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'is_retryable_failure' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'apply_filters' )->andReturnUsing(
			function( $filter, $default ) { return $default; }
		);

		$result = array( 'charge' => array( 'failure_code' => 'insufficient_fund' ) );
		$this->assertFalse( $method->invoke( null, $result ) );
	}

	public function test_is_retryable_failure_returns_false_when_no_charge() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'is_retryable_failure' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$this->assertFalse( $method->invoke( null, array() ) );
	}

	public function test_is_retryable_failure_returns_false_when_no_failure_code() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'is_retryable_failure' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$result = array( 'charge' => array( 'status' => 'failed' ) );
		$this->assertFalse( $method->invoke( null, $result ) );
	}

	// ─── is_callback_replay Tests ────────────────────────────────────────

	public function test_is_callback_replay_returns_true_when_resolved_and_no_state() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'is_callback_replay' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'yes' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( '' );

		$this->assertTrue( $method->invoke( null, $order ) );
	}

	public function test_is_callback_replay_returns_false_when_not_resolved() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'is_callback_replay' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( '' );

		$this->assertFalse( $method->invoke( null, $order ) );
	}

	// ─── set_transaction_id Tests ────────────────────────────────────────

	public function test_set_transaction_id_does_nothing_when_charge_id_empty() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'set_transaction_id' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldNotReceive( 'set_transaction_id' );

		$method->invoke( null, $order, array() );
		$this->assertTrue( true );
	}

	public function test_set_transaction_id_does_nothing_when_already_set() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'set_transaction_id' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_transaction_id' )->andReturn( 'chrg_test_123' );
		$order->shouldNotReceive( 'set_transaction_id' );

		$method->invoke( null, $order, array( 'id' => 'chrg_test_123' ) );
		$this->assertTrue( true );
	}

	public function test_set_transaction_id_sets_new_id() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'set_transaction_id' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_transaction_id' )->andReturn( '' );
		$order->shouldReceive( 'set_transaction_id' )->once()->with( 'chrg_test_456' );

		$method->invoke( null, $order, array( 'id' => 'chrg_test_456' ) );
	}

	// ─── complete with resolver Tests ────────────────────────────────────

	public function test_complete_redirects_to_checkout_when_no_order() {
		$_GET = array();

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
		) );
		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( 'https://shop.test/checkout/' )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::complete();
	}

	public function test_cancel_redirects_to_checkout_when_no_order() {
		$_GET = array();

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
		) );
		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->with( 'https://shop.test/checkout/' )
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		Omise_UPA_Callback::cancel();
	}

	// ─── recheck_pending_order Tests ─────────────────────────────────────

	public function test_recheck_pending_order_returns_early_for_empty_order_id() {
		Monkey\Functions\expect( 'absint' )->once()->with( 0 )->andReturn( 0 );

		// Should return without doing anything
		Omise_UPA_Callback::recheck_pending_order( 0 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_returns_early_when_order_not_found() {
		Monkey\Functions\expect( 'absint' )->once()->andReturn( 123 );
		Monkey\Functions\expect( 'wc_get_order' )->once()->with( 123 )->andReturn( false );

		Omise_UPA_Callback::recheck_pending_order( 123 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_returns_early_when_already_paid() {
		Monkey\Functions\expect( 'absint' )->once()->andReturn( 123 );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->andReturn( true );

		Monkey\Functions\expect( 'wc_get_order' )->once()->with( 123 )->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 123 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_returns_early_when_already_resolved() {
		Monkey\Functions\expect( 'absint' )->once()->andReturn( 123 );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'yes' );

		Monkey\Functions\expect( 'wc_get_order' )->once()->with( 123 )->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 123 );
		$this->assertTrue( true );
	}

	public function test_recheck_pending_order_returns_early_for_terminal_order_status() {
		Monkey\Functions\expect( 'absint' )->once()->andReturn( 123 );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'is_paid' )->andReturn( false );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'has_status' )
			->with( array( 'failed', 'cancelled', 'refunded' ) )
			->andReturn( true );

		Monkey\Functions\expect( 'wc_get_order' )->once()->with( 123 )->andReturn( $order );

		Omise_UPA_Callback::recheck_pending_order( 123 );
		$this->assertTrue( true );
	}

	// ─── should_offer_retry Tests ────────────────────────────────────────

	public function test_should_offer_retry_returns_false_for_non_retryable() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'should_offer_retry' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );

		$result = array( 'charge' => array( 'failure_code' => 'insufficient_fund' ) );

		Monkey\Functions\expect( 'apply_filters' )->andReturnUsing(
			function( $filter, $default ) { return $default; }
		);

		$this->assertFalse( $method->invoke( null, $order, $result ) );
	}

	public function test_should_offer_retry_returns_true_for_retryable_under_limit() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'should_offer_retry' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Callback::META_RETRY_ATTEMPTS )
			->andReturn( '0' );

		Monkey\Functions\expect( 'absint' )->andReturn( 0 );
		Monkey\Functions\expect( 'apply_filters' )->andReturnUsing(
			function( $filter, $default ) { return $default; }
		);

		$result = array( 'charge' => array( 'failure_code' => 'processing_error' ) );

		$this->assertTrue( $method->invoke( null, $order, $result ) );
	}

	// ─── get_recheck_attempt_limit Tests ─────────────────────────────────

	public function test_get_recheck_attempt_limit_returns_filtered_value() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_recheck_attempt_limit' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with( 'omise_upa_recheck_attempt_limit', 96 )
			->andReturn( 50 );

		$this->assertSame( 50, $method->invoke( null ) );
	}

	public function test_get_recheck_attempt_limit_falls_back_to_96_for_invalid_value() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_recheck_attempt_limit' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with( 'omise_upa_recheck_attempt_limit', 96 )
			->andReturn( -1 );

		$this->assertSame( 96, $method->invoke( null ) );
	}

	// ─── get_recheck_delay_seconds Tests ─────────────────────────────────

	public function test_get_recheck_delay_seconds_uses_progressive_default() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_recheck_delay_seconds' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'absint' )->andReturnUsing( function( $v ) { return abs( (int) $v ); } );
		Monkey\Functions\expect( 'apply_filters' )->andReturnUsing(
			function( $filter, $default ) { return $default; }
		);

		// Attempt 1: 15*1 = 15
		$this->assertSame( 15, $method->invoke( null, 1 ) );
		// Attempt 6: 15*6 = 90
		$this->assertSame( 90, $method->invoke( null, 6 ) );
		// Attempt 7: caps at 900
		$this->assertSame( 900, $method->invoke( null, 7 ) );
	}

	// ─── get_retry_attempt_limit Tests ───────────────────────────────────

	public function test_get_retry_attempt_limit_returns_default() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'get_retry_attempt_limit' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with( 'omise_upa_retry_attempt_limit', 1 )
			->andReturn( 1 );

		$this->assertSame( 1, $method->invoke( null ) );
	}

	// ─── register_hooks Test ─────────────────────────────────────────────

	public function test_register_hooks_adds_three_actions() {
		Monkey\Functions\expect( 'add_action' )->times( 3 );

		Omise_UPA_Callback::register_hooks();
		$this->assertTrue( true );
	}

	// ─── handle_invalid_state Tests ──────────────────────────────────────

	public function test_handle_invalid_state_redirects_for_replay() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'handle_invalid_state' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'yes' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( '' );

		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		$method->invoke( null, $order, 'Invalid state' );
	}

	public function test_handle_invalid_state_adds_note_and_redirects_for_non_replay() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'handle_invalid_state' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_RESOLVED )
			->andReturn( 'no' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'some_state' );
		$order->shouldReceive( 'add_order_note' )->once()->with( 'Invalid state' );

		Monkey\Functions\expect( 'wc_get_checkout_url' )->once()->andReturn( 'https://shop.test/checkout/' );
		Monkey\Functions\expect( 'nocache_headers' )->once();
		Monkey\Functions\expect( 'wp_safe_redirect' )
			->once()
			->andThrow( new Exception( 'redirected' ) );

		$this->expectExceptionMessage( 'redirected' );
		$method->invoke( null, $order, 'Invalid state' );
	}

	// ─── mark_retryable_failure Tests ────────────────────────────────────

	public function test_mark_retryable_failure_increments_retry_counter() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'mark_retryable_failure' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'absint' )->andReturn( 0 );
		Monkey\Functions\expect( 'apply_filters' )->andReturnUsing(
			function( $filter, $default ) { return $default; }
		);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->with( Omise_UPA_Callback::META_RETRY_ATTEMPTS )
			->andReturn( '0' );
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'update_meta_data' )
			->with( Omise_UPA_Callback::META_RETRY_ATTEMPTS, '1' )
			->once();
		$order->shouldReceive( 'update_meta_data' )
			->with( 'is_omise_payment_resolved', 'no' )
			->once();
		$order->shouldReceive( 'update_meta_data' )
			->with( Omise_UPA_Session_Service::META_RESOLVED, 'no' )
			->once();
		$order->shouldReceive( 'delete_meta_data' )
			->with( Omise_UPA_Session_Service::META_STATE )
			->once();
		$order->shouldReceive( 'update_status' )->with( 'pending' )->once();
		$order->shouldReceive( 'save' )->once();

		$method->invoke( null, $order, 'Processing error' );
	}

	// ─── retry_pending_resolution_inline Tests ───────────────────────────

	public function test_retry_pending_resolution_inline_returns_immediately_for_non_pending() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'retry_pending_resolution_inline' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-payment-resolver.php';

		$order    = Mockery::mock( 'WC_Order' );
		$resolver = Mockery::mock( 'Omise_UPA_Payment_Resolver' );
		$result   = array( 'state' => Omise_UPA_Payment_Resolver::STATE_SUCCESSFUL );

		$returned = $method->invoke( null, $order, $resolver, $result );
		$this->assertSame( Omise_UPA_Payment_Resolver::STATE_SUCCESSFUL, $returned['state'] );
	}

	public function test_retry_pending_resolution_inline_returns_immediately_for_empty_result() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'retry_pending_resolution_inline' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$order    = Mockery::mock( 'WC_Order' );
		$resolver = Mockery::mock( 'Omise_UPA_Payment_Resolver' );

		$returned = $method->invoke( null, $order, $resolver, array() );
		$this->assertSame( array(), $returned );
	}

	// ─── schedule_pending_recheck Tests ──────────────────────────────────

	public function test_schedule_pending_recheck_does_nothing_when_over_limit() {
		$method = new ReflectionMethod( Omise_UPA_Callback::class, 'schedule_pending_recheck' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'absint' )->andReturn( 100 );
		Monkey\Functions\expect( 'apply_filters' )
			->with( 'omise_upa_recheck_attempt_limit', 96 )
			->andReturn( 96 );

		// WC should not be called
		$method->invoke( null, 123, 100 );
		$this->assertTrue( true );
	}
}
