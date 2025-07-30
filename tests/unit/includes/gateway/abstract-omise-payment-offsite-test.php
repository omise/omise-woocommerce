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
		require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offsite.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-atome.php';

		Monkey\Functions\stubs(
			[
				'wp_enqueue_script',
				'wp_kses',
				'plugins_url',
				'add_action',
        'sanitize_text_field' => null,
				'plugin_dir_path' => __DIR__ . '/../../../../',
			]
		);

		$this->mockOmiseSetting( 'pkey_xxx', 'skey_xxx' );
		$this->mockRedirectUrl( $this->return_uri );
		load_plugin();
	}

	public function perform_charge_test( $instance ) {
		$expected_amount = 999999;
		$expected_currency = 'thb';

		$charge = [
			'object' => 'charge',
			'id' => 'chrg_test_no1t4tnemucod0e51mo',
			'location' => '/charges/chrg_test_no1t4tnemucod0e51mo',
			'amount' => $expected_amount,
			'currency' => $expected_currency,
		];
		$order = $this->getOrderMock( $expected_amount, $expected_currency );

		// Create a mock for OmiseCharge
		$charge_api_mock = Mockery::mock( 'overload:OmiseCharge' );
		$charge_api_mock->shouldReceive( 'create' )->once()->andReturn( $charge );

		// Create a mock for WC_Product
		$wc_product = Mockery::mock( 'overload:WC_Product' );
		$wc_product->shouldReceive( 'get_sku' )
			->once()
			->andReturn( 'sku_1234' );

		$result = $instance->charge( 'order_123', $order );

		$this->assertEquals( $charge, $result );
	}
}
