<?php

require_once __DIR__ . '/bootstrap-test-setup.php';

use Brain\Monkey;

abstract class Omise_Offsite_Test extends Bootstrap_Test_Setup
{
    public $sourceType;

    protected function setUp(): void
    {
        parent::setUp();

        // Mocking the parent class
        $offsite = Mockery::mock('overload:Omise_Payment_Offsite');
        $offsite->shouldReceive('init_settings');
        $offsite->shouldReceive('get_option');
        $offsite->shouldReceive('get_provider');
        $offsite->shouldReceive('payment_fields');
        $offsite->shouldReceive('public_key')
            ->andReturn('pkey_test_123');
        $offsite->shouldReceive('is_available')
            ->andReturn(true);
        $offsite->shouldReceive('build_charge_request')
            ->andReturn([
                'source' => [ 'type' => $this->sourceType ]
            ]);
    }

    public function getChargeTest($classObj)
    {
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';

        Monkey\Functions\expect('wc_clean')->andReturn($expectedAmount);

        $expectedRequest = [
            "object" => "charge",
            "id" => "chrg_test_no1t4tnemucod0e51mo",
            "location" => "/charges/chrg_test_no1t4tnemucod0e51mo",
            "amount" => $expectedAmount,
            "currency" => $expectedCurrency
        ];

        // Create a mock for OmiseCharge
        $chargeMock = Mockery::mock('overload:OmiseCharge');
        $chargeMock->shouldReceive('create')->once()->andReturn($expectedRequest);

        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $wcProduct = Mockery::mock('overload:WC_Product');
        $wcProduct->shouldReceive('get_sku')
            ->once()
            ->andReturn('sku_1234');

        $orderId = 'order_123';
        $result = $classObj->charge($orderId, $orderMock);
        $this->assertEquals($expectedAmount, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
    }
}
