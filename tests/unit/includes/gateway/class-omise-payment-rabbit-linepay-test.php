<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_RabbitLinePay_Test extends Omise_Offsite_Test
{
    private $obj;

    protected function setUp(): void
    {
        $this->sourceType = 'mobile_banking_ocbc';
        parent::setUp();
        Monkey\Functions\expect('add_action');
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-rabbit-linepay.php';
        $this->obj = new Omise_Payment_RabbitLinePay();
    }

    /**
     * @test
     */
    public function testCharge()
    {
        $this->getChargeTest($this->obj);
    }
}
