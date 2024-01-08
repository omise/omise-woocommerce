<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Truemoney_Test extends Omise_Offsite_Test
{
    private $obj;

    public function setUp(): void
    {
        $this->sourceType = 'truemoney';
        parent::setUp();
        Brain\Monkey\Functions\expect('is_admin')
			->with('123')
			->andReturn(true);
		Brain\Monkey\Functions\expect('is_checkout')
			->with('123')
			->andReturn(true);
		Brain\Monkey\Functions\expect('is_wc_endpoint_url')
			->with('123')
			->andReturn(true);
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-truemoney.php';
        require_once __DIR__ . '/../../../../includes/class-omise-capabilities.php';

        $this->obj = new Omise_Payment_Truemoney();
    }

    public function testGetChargeRequest()
    {
        // set source type to truemoney wallet
        $this->obj->source_type = 'truemoney';
        $orderId = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['omise_phone_number_default'] = true;
        $result = $this->obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($orderMock->get_billing_phone(), $result['source']['phone_number']);
    }

    public function testGetChargeRequestWhenCustomerOverridesDefaultPhone()
    {
        $orderId = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['omise_phone_number_default'] = false;
        $_POST['omise_phone_number'] = '1234567890';
        
        $result = $this->obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($this->sourceType, $result['source']['type']);
    }

    public function testCharge()
    {
        $_POST['omise_phone_number_default'] = true;
        $this->getChargeTest($this->obj);
    }
}
