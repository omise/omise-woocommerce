<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Truemoney_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-truemoney.php';
    }

    public function getOrderMock($expectedAmount, $expectedCurrency)
    {
        // Create a mock of the $order object
        $orderMock = Mockery::mock('WC_Order');

        // Define expectations for the mock
        $orderMock->shouldReceive('get_currency')
            ->andReturn($expectedCurrency);
        $orderMock->shouldReceive('get_total')
            ->andReturn($expectedAmount);  // in units
        $orderMock->shouldReceive('add_meta_data');
        $orderMock->shouldReceive('get_billing_phone')
            ->andReturn('1234567890');
        return $orderMock;
    }

    public function testGetChargeRequest()
    {
        $obj = new Omise_Payment_Truemoney();

        $orderId = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['omise_phone_number_default'] = true;
        $result = $obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($orderMock->get_billing_phone(), $result['source']['phone_number']);

        unset($_POST['omise_phone_number_default']);
        unset($obj);
    }
}
