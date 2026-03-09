<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

if ( ! class_exists( 'Omise_Payment' ) ) {
	class Omise_Payment {}
}

if ( ! class_exists( 'Omise_Payment_Offsite' ) ) {
	class Omise_Payment_Offsite extends Omise_Payment {}
}

if ( ! class_exists( 'Omise_Payment_Offline' ) ) {
	class Omise_Payment_Offline extends Omise_Payment {}
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_UPA_Feature_Flag_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-feature-flag.php';
	}

	public function test_is_enabled_for_order_returns_true_for_supported_gateway() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->once()->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->once()->andReturn( true );

		$gateway = new Omise_Payment_Offsite();
		$order   = Mockery::mock( 'WC_Order' );

		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with( 'omise_upa_enabled_for_order', true, $gateway, $order )
			->andReturn( true );

		$this->assertTrue( Omise_UPA_Feature_Flag::is_enabled_for_order( $gateway, $order ) );
	}

	public function test_is_enabled_for_order_returns_false_for_unsupported_gateway() {
		$gateway = new stdClass();
		$order   = Mockery::mock( 'WC_Order' );

		$this->assertFalse( Omise_UPA_Feature_Flag::is_enabled_for_order( $gateway, $order ) );
	}

	public function test_is_enabled_for_order_allows_filter_to_disable_feature() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->once()->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->once()->andReturn( true );

		$gateway = new Omise_Payment_Offline();
		$order   = Mockery::mock( 'WC_Order' );

		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with( 'omise_upa_enabled_for_order', true, $gateway, $order )
			->andReturn( false );

		$this->assertFalse( Omise_UPA_Feature_Flag::is_enabled_for_order( $gateway, $order ) );
	}
}
