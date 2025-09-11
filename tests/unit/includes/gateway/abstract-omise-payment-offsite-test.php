<?php

require_once __DIR__ . '/bootstrap-test-setup.php';

use Brain\Monkey;

abstract class Omise_Payment_Offsite_Test extends Bootstrap_Test_Setup {

	protected $return_uri = 'https://abc.com/order/complete';

	protected function setUp(): void {
		parent::setUp();

		/**
		 * FIXME: After all offsite tests are moved to use this class:
		 *  * Move this to the bootstrap.php file.
		 *  * Remove @runTestsInSeparateProcesses from all offsite tests.
		 */
		require_once __DIR__ . '/../../../../includes/backends/class-omise-backend.php';
		require_once __DIR__ . '/../../../../includes/backends/class-omise-backend-installment.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offsite.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-atome.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-installment.php';

		Monkey\Functions\stubs(
			[
				'wp_enqueue_script',
				'wp_kses',
				'plugins_url',
				'add_action',
				'wc_clean' => null,
				'sanitize_text_field' => null,
				'plugin_dir_path' => __DIR__ . '/../../../../',
			]
		);

		$this->mockOmiseSetting( 'pkey_test_123', 'skey_test_123' );
		$this->mockRedirectUrl( $this->return_uri );
		load_plugin();
	}

	/**
	 * Few methods inside payment gateway class is not available in the test environment.
	 * This method is a helper to mock those methods.
	 * Other methods remains unmocked so that we can test the actual behavior of the class.
	 *
	 * @param string $class_name
	 * @return Mockery\LegacyMockInterface
	 */
	protected function mock_payment_class( $class_name ) {
		$mock = Mockery::mock( $class_name )->makePartial();
		$mock->shouldReceive( 'init_settings' );
		$mock->shouldReceive( 'get_option' );
		$mock->__construct();

		return $mock;
	}

	protected function perform_charge_test( $instance, $order, $test_charge_fn = null ) {
		$charge = [
			'object' => 'charge',
			'id' => 'chrg_test_no1t4tnemucod0e51mo',
			'location' => '/charges/chrg_test_no1t4tnemucod0e51mo',
			'amount' => $order->get_total(),
			'currency' => $order->get_currency(),
		];

		// Create a mock for OmiseCharge
		$charge_api_mock = Mockery::mock( 'overload:OmiseCharge' );

		if ( $test_charge_fn === null ) {
			$charge_api_mock->shouldReceive( 'create' )->once()->andReturn( $charge );
		} else {
			$charge_api_mock->shouldReceive( 'create' )
				->once()
				->with( Mockery::on( $test_charge_fn ) )
				->andReturn( $charge );
		}

		// Create a mock for WC_Product
		$wc_product = Mockery::mock( 'overload:WC_Product' );
		$wc_product->shouldReceive( 'get_sku' )
			->once()
			->andReturn( 'sku_1234' );

		$result = $instance->charge( $order->get_id(), $order );

		$this->assertEquals( $charge, $result );
	}
}
