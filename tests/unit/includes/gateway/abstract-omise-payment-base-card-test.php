<?php

use PHPUnit\Framework\TestCase;

class Omise_Payment_Base_Card_Test extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-base-card.php';
        // Mocking the parent class
        // $omisePayment = Mockery::mock('overload:Omise_Payment');
        // $omisePayment->shouldReceive('')
    }

    public function prepareChargeData()
    {
        $mock = Mockery::mock('Omise_Payment_Base_Card')->makePartial();

        // $mock = $this->getMockBuilder('Omise_Payment_Base_Card')
        //     ->onlyMethods(['prepareChargeData'])
        //     ->getMock();

        // $reflectionClass = new \ReflectionClass($mock);

        // $prepareChargeData = $reflectionClass->getMethod('prepareChargeData');
        // $prepareChargeData->setAccessible(true);

        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $order_id = 'order_123';
        $omise_customer_id = 'cust_123';
        $card_id = 'card_123';
        $token = 'token_123';

        // Create a mock of the $order object
        $orderMock = Mockery::mock('WC_Order');

        // Define expectations for the mock
        $orderMock->shouldReceive('get_currency')
            ->andReturn($expectedCurrency);
        $orderMock->shouldReceive('get_total')
            ->andReturn($expectedAmount/100);  // in units
        $orderMock->shouldReceive('add_meta_data');

        $result = $mock->prepareChargeData($order_id, $orderMock, $omise_customer_id, $card_id, $token);

        echo var_dump('<pre>' . print_r($result, true) . '<pre>');

        // $result = $prepareChargeData->invoke($mock, $order_id, $orderMock, $omise_customer_id, $card_id, $token);
    }
}