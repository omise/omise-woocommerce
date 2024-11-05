<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Internetbanking_Test extends Omise_Offsite_Test
{
    protected function setUp(): void
    {
        $this->sourceType = 'fpx';
        parent::setUp();
        Monkey\Functions\expect('wp_kses');
        Monkey\Functions\expect('add_action');
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-internetbanking.php';
    }

    public function testCharge()
    {
        $_POST['omise-offsite'] = 'internet_banking';
        $obj = new Omise_Payment_Internetbanking();
        $this->getChargeTest($obj);
    }
}
