<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Boost_Test extends Omise_Payment_Offsite_Test {

	private $omise_boost;

	protected function setUp(): void {
		parent::setUp();

		$this->omise_boost = $this->mock_payment_class( Omise_Payment_Boost::class );
	}

	public function test_boost_get_icon() {
		Monkey\Functions\expect( 'apply_filters' )
		->once()
		->with(
			'woocommerce_gateway_icon',
			"<img src='../assets/images/boost.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='Boost' />",
			'omise_boost'
		);

		$this->omise_boost->get_icon();
	}
}
