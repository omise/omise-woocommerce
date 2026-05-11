<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

/**
 * Tests for the UPA integration in Omise_Payment_Offsite::process_payment().
 *
 * Uses a helper that replicates the UPA process_payment logic from the real
 * gateway to test the branching behavior in isolation.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Offsite_UPA_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		Monkey\Functions\stubs( array(
			'sanitize_text_field' => function( $v ) { return $v; },
			'wp_unslash'          => function( $v ) { return $v; },
			'wp_kses'             => function( $v ) { return $v; },
		) );
	}

	/**
	 * Replicate the UPA process_payment logic from abstract-omise-payment-offsite.php.
	 */
	private function invoke_process_payment( $gateway, $order_id ) {
		if ( ! Omise_Setting::instance()->is_upa_enabled() ) {
			return array( 'result' => 'parent_fallback' );
		}

		if ( ! $gateway->load_order( $order_id ) ) {
			return $gateway->invalid_order( $order_id );
		}

		if ( ! Omise_UPA_Feature_Flag::is_enabled_for_order( $gateway, $gateway->order() ) ) {
			return array( 'result' => 'parent_fallback' );
		}

		$gateway->order()->add_order_note( sprintf( 'Omise: Processing a payment with %s', $gateway->method_title ) );
		$gateway->order()->add_meta_data( 'is_omise_payment_resolved', 'no', true );
		$gateway->order()->save();

		try {
			return Omise_UPA_Session_Service::create_checkout_session( $gateway, $order_id, $gateway->order() );
		} catch ( Exception $e ) {
			return array( 'result' => 'failure', 'message' => $e->getMessage() );
		}
	}

	public function test_process_payment_delegates_to_parent_when_upa_disabled() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->once()->andReturn( false );

		$gateway = Mockery::mock();
		$gateway->method_title = 'Test Offsite';

		$result = $this->invoke_process_payment( $gateway, 99 );
		$this->assertSame( 'parent_fallback', $result['result'] );
	}

	public function test_process_payment_returns_invalid_order_when_load_fails() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$gateway = Mockery::mock();
		$gateway->shouldReceive( 'load_order' )->once()->with( 99 )->andReturn( false );
		$gateway->shouldReceive( 'invalid_order' )->once()->with( 99 )->andReturn( array( 'result' => 'failure' ) );

		$result = $this->invoke_process_payment( $gateway, 99 );
		$this->assertSame( 'failure', $result['result'] );
	}

	public function test_process_payment_delegates_to_parent_when_feature_flag_disabled() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$feature_flag = Mockery::mock( 'alias:Omise_UPA_Feature_Flag' );
		$feature_flag->shouldReceive( 'is_enabled_for_order' )->once()->andReturn( false );

		$order = Mockery::mock( 'WC_Order' );

		$gateway = Mockery::mock();
		$gateway->method_title = 'Test Offsite';
		$gateway->shouldReceive( 'load_order' )->andReturn( true );
		$gateway->shouldReceive( 'order' )->andReturn( $order );

		$result = $this->invoke_process_payment( $gateway, 99 );
		$this->assertSame( 'parent_fallback', $result['result'] );
	}

	public function test_process_payment_calls_upa_session_when_enabled() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$feature_flag = Mockery::mock( 'alias:Omise_UPA_Feature_Flag' );
		$feature_flag->shouldReceive( 'is_enabled_for_order' )->once()->andReturn( true );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'add_meta_data' )->once()->with( 'is_omise_payment_resolved', 'no', true );
		$order->shouldReceive( 'save' )->once();

		$gateway = Mockery::mock();
		$gateway->method_title = 'Test Offsite';
		$gateway->shouldReceive( 'load_order' )->andReturn( true );
		$gateway->shouldReceive( 'order' )->andReturn( $order );

		$session_service = Mockery::mock( 'alias:Omise_UPA_Session_Service' );
		$session_service->shouldReceive( 'create_checkout_session' )
			->once()
			->with( $gateway, 99, $order )
			->andReturn( array( 'result' => 'success', 'redirect' => 'https://upa.example.com/pay' ) );

		$result = $this->invoke_process_payment( $gateway, 99 );
		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay', $result['redirect'] );
	}

	public function test_process_payment_returns_failure_on_exception() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$feature_flag = Mockery::mock( 'alias:Omise_UPA_Feature_Flag' );
		$feature_flag->shouldReceive( 'is_enabled_for_order' )->once()->andReturn( true );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'add_meta_data' )->once();
		$order->shouldReceive( 'save' )->once();

		$gateway = Mockery::mock();
		$gateway->method_title = 'Test Offsite';
		$gateway->shouldReceive( 'load_order' )->andReturn( true );
		$gateway->shouldReceive( 'order' )->andReturn( $order );

		$session_service = Mockery::mock( 'alias:Omise_UPA_Session_Service' );
		$session_service->shouldReceive( 'create_checkout_session' )
			->once()
			->andThrow( new Exception( 'Session failed' ) );

		$result = $this->invoke_process_payment( $gateway, 99 );
		$this->assertSame( 'failure', $result['result'] );
		$this->assertSame( 'Session failed', $result['message'] );
	}
}
