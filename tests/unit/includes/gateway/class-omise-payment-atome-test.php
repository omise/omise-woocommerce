<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Atome_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'atome';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-atome.php';

        if (!function_exists('wp_enqueue_script')) {
            function wp_enqueue_script() {}
        }

        if (!function_exists('plugins_url')) {
            function plugins_url() {}
        }

        // dummy version
        if (!defined('WC_VERSION')) {
            define('WC_VERSION', '1.0.0');
        }
    }

    public function testGetChargeRequest()
    {
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderId = 'order_123';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $wcProduct = Mockery::mock('overload:WC_Product');
        $wcProduct->shouldReceive('get_sku')
            ->once()
            ->andReturn('sku_1234');

        $_POST['omise_atome_phone_default'] = true;

        $obj = new Omise_Payment_Atome();
        $result = $obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($this->sourceType, $result['source']['type']);

        unset($_POST['source']);
        unset($obj);
    }

    public function testCharge()
    {
        $orderId = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
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

        $_POST['omise_atome_phone_default'] = true;

        $obj = new Omise_Payment_Atome();
        $result = $obj->charge($orderId, $orderMock);

        $this->assertEquals($expectedAmount, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);

        unset($_POST['source']);
        unset($obj);
    }
}
