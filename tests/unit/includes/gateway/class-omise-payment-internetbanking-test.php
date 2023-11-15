<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Internetbanking_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'fpx';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-internetbanking.php';
    }

    public function testCharge()
    {
        $_POST['omise-offsite'] = 'internet_banking';
        $obj = new Omise_Payment_Internetbanking();
        $this->getChargeTest($obj);
    }
}
