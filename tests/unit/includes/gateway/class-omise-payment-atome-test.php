<?php

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Atome_Test extends Omise_Payment_Offsite_Test {

	private $omise_atome;

	protected function setUp(): void {
		$this->sourceType = 'atome';
		parent::setUp();

		$this->omise_atome = Mockery::mock( Omise_Payment_Atome::class )->makePartial();
		$this->omise_atome->shouldReceive( 'init_settings' );
		$this->omise_atome->shouldReceive( 'get_option' );
		$this->omise_atome->__construct();
	}

	public function test_atome_get_charge_request() {
		$order_amount = 4566;
		$order_currency = 'thb';
		$order_id = 'order_123';
		$order_mock = $this->getOrderMock( $order_amount, $order_currency );

		$wc_product = Mockery::mock( 'overload:WC_Product' );
		$wc_product->shouldReceive( 'get_sku' )
			->once()
			->andReturn( 'sku_1234' );

		$_POST['omise_atome_phone_default'] = true;

		$result = $this->omise_atome->get_charge_request( $order_id, $order_mock );

		$this->assertEquals( 456600, $result['amount'] );
		$this->assertEquals( $order_currency, $result['currency'] );
		$this->assertEquals( $order_id, $result['metadata']['order_id'] );
		$this->assertEquals( $this->return_uri, $result['return_uri'] );

		$expected_source = [
			'type' => 'atome',
			'phone_number' => $order_mock->get_billing_phone(),
			'items' => [
				[
					'name' => 'T Shirt',
          'amount' => 60000,
          'quantity' => 1,
					'sku' => 'sku_1234',
				]
			],
			'shipping' => [
				'country' => 'Thailand',
				'city' => 'Bangkok',
				'postal_code' => '10110',
				'state' => 'Bangkok',
				'street1' => 'Sukumvit Road'
			]
		];
		$this->assertEquals( $expected_source, $result['source'] );
	}

	public function test_atome_charge() {
		$_POST['omise_atome_phone_default'] = true;

		$this->testCharge( $this->omise_atome );
	}
}
