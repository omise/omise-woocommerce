<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Shopeepay_Test extends Omise_Payment_Offsite_Test {

	private $omise_shopeepay;

	protected function setUp(): void {
		parent::setUp();

		// Mock Omise Capability for ShopeePay source type.
		$this->omise_capability_mock = Mockery::mock( 'alias:Omise_Capability' );
		$this->omise_capability_mock->shouldReceive( 'retrieve' )->once();

		$this->omise_shopeepay = $this->mock_payment_class( Omise_Payment_Shopeepay::class );
	}

	public function test_shopeepay_get_icon() {
		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with(
				'woocommerce_gateway_icon',
				"<img src='../assets/images/shopeepay.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='ShopeePay' />",
				'omise_shopeepay'
			);

		$this->omise_shopeepay->get_icon();
	}
}
