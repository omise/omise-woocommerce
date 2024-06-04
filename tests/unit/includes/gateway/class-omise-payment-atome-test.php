<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

use Brain\Monkey;

class Omise_Payment_Atome_Test extends Omise_Offsite_Test
{
    protected function setUp(): void
    {
        $this->sourceType = 'atome';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-atome.php';

        Monkey\Functions\expect('wp_enqueue_script');
        Monkey\Functions\expect('wp_kses');
        Monkey\Functions\expect('plugins_url');

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
    }

    public function testCharge()
    {
        Monkey\Functions\expect('add_action');
        $_POST['omise_atome_phone_default'] = true;
        $obj = new Omise_Payment_Atome();
        $this->getChargeTest($obj);
    }
}
