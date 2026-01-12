<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Maybank_QR_Test extends Omise_Payment_Offsite_Test {

	private $omise_maybank_qr;

	protected function setUp(): void {
		parent::setUp();

		$this->omise_maybank_qr = $this->mock_payment_class( Omise_Payment_Maybank_QR::class );
	}

	public function test_maybank_qr_get_icon() {
		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with(
				'woocommerce_gateway_icon',
				"<img src='../assets/images/maybank-qr.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='Maybank QRPay' />",
				'omise_maybank_qr'
			);

		$this->omise_maybank_qr->get_icon();
	}
}
