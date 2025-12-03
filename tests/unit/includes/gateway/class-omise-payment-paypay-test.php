<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Paypay_Test extends Omise_Payment_Offsite_Test {

	private $omise_paypay;

	protected function setUp(): void {
		parent::setUp();

		$this->omise_paypay = $this->mock_payment_class( Omise_Payment_Paypay::class );
	}

	public function test_paypay_get_icon() {
		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with(
				'woocommerce_gateway_icon',
				"<img src='../assets/images/paypay.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='PayPay' />",
				'omise_paypay'
			);

		$this->omise_paypay->get_icon();
	}
}
