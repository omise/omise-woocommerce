<?php

require_once __DIR__ . '/bootstrap-test-setup.php';

use Brain\Monkey;

abstract class Omise_Payment_Offsite_Test extends Bootstrap_Test_Setup {

	public $sourceType;

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
			]
		);

		$this->mockOmiseSetting( 'pkey_xxx', 'skey_xxx' );
		$this->mockRedirectUrl( 'https://abc.com/order/complete' );
		load_plugin();
	}

	// TODO: Update this test steps
	public function getChargeTest( $classObj ) {
		$expectedAmount = 999999;
		$expectedCurrency = 'thb';

		Monkey\Functions\expect( 'wc_clean' )->andReturn( $expectedAmount );

		$expectedRequest = [
			'object' => 'charge',
			'id' => 'chrg_test_no1t4tnemucod0e51mo',
			'location' => '/charges/chrg_test_no1t4tnemucod0e51mo',
			'amount' => $expectedAmount,
			'currency' => $expectedCurrency,
		];

		// Create a mock for OmiseCharge
		$chargeMock = Mockery::mock( 'overload:OmiseCharge' );
		$chargeMock->shouldReceive( 'create' )->once()->andReturn( $expectedRequest );

		$orderMock = $this->getOrderMock( $expectedAmount, $expectedCurrency );

		$wcProduct = Mockery::mock( 'overload:WC_Product' );
		$wcProduct->shouldReceive( 'get_sku' )
			->once()
			->andReturn( 'sku_1234' );

		$orderId = 'order_123';
		$result = $classObj->charge( $orderId, $orderMock );
		$this->assertEquals( $expectedAmount, $result['amount'] );
		$this->assertEquals( $expectedCurrency, $result['currency'] );
	}
}
