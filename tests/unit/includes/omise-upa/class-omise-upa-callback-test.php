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
}
