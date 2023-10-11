<?php

require_once __DIR__ . '/bootstrap-test-setup.php';

class Omise_Payment_Offline_Test extends Bootstrap_Test_Setup
{
    public function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offline.php';
    }

    public function charge()
    {
        $orderMock = $this->getOrderMock(99999, 'THB');
        $mock = Mockery::mock('Omise_Payment_Offline')->makePartial();

        $result = $mock->charge('order_123', $orderMock);

        var_dump(print_r($result, true));
    }
}
