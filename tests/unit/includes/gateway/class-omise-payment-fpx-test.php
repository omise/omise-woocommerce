<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_FPX_Test extends Omise_Payment_Offsite_Test {

	private $omise_fpx;

	protected function setUp(): void {
		parent::setUp();

		$this->omise_fpx = $this->mock_payment_class( Omise_Payment_FPX::class );
	}

	public function test_fpx_charge() {
		$order = $this->getOrderMock( 999999, 'THB' );
		$_POST['source'] = [ 'bank' => 'SCB' ];

		$this->perform_charge_test( $this->omise_fpx, $order );
	}

	public function test_fpx_get_icon() {
		Monkey\Functions\expect( 'apply_filters' )
		->once()
		->with(
			'woocommerce_gateway_icon',
			"<img src='../assets/images/fpx.svg' class='Omise-Image' style='width: 45px; max-height: 45px;' alt='FPX' />",
			'omise_fpx'
		);

		$this->omise_fpx->get_icon();
	}
}
