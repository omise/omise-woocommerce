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
		$expectedAmount = 999999;
		$expectedCurrency = 'thb';
		$orderId = 'order_123';
		$orderMock = $this->getOrderMock( $expectedAmount, $expectedCurrency );

		$wcProduct = Mockery::mock( 'overload:WC_Product' );
		$wcProduct->shouldReceive( 'get_sku' )
			->once()
			->andReturn( 'sku_1234' );

		$_POST['omise_atome_phone_default'] = true;

		$result = $this->omise_atome->get_charge_request( $orderId, $orderMock );

		// TODO: Update this assertion
		$this->assertEquals( $this->sourceType, $result['source']['type'] );
	}

	public function test_atome_charge() {
		$_POST['omise_atome_phone_default'] = true;
		$obj = new Omise_Payment_Atome();
		$this->getChargeTest( $obj );
	}
}
