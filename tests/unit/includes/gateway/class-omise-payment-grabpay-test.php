<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Grabpay_Test extends Omise_Payment_Offsite_Test {

	private $omise_grabpay;

	protected function setUp(): void {
		parent::setUp();

		$this->omise_grabpay = $this->mock_payment_class( Omise_Payment_GrabPay::class );
	}

	public function test_grabpay_get_icon() {
		Monkey\Functions\expect( 'apply_filters' )
		->once()
		->with(
			'woocommerce_gateway_icon',
			"<img src='../assets/images/grabpay.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='GrabPay' />",
			'omise_grabpay'
		);

		$this->omise_grabpay->get_icon();
	}
}
