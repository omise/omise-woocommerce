<?php

use PHPUnit\Framework\TestCase;

class Omise_Payment_Offline_Test extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offline.php';
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
        return $orderMock;
    }

    public function charge()
    {
        $orderMock = $this->getOrderMock(99999, 'THB');
        $mock = Mockery::mock('Omise_Payment_Offline')->makePartial();

        $result = $mock->charge('order_123', $orderMock);

        var_dump(print_r($result, true));

    }
}
