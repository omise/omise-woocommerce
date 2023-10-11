<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_DuitNow_OBW_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'duitnow_obw';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-duitnow-obw.php';
    }

    public function testCharge()
    {
        $_POST['source'] = ['bank' => 'SCB'];
        $obj = new Omise_Payment_DuitNow_OBW();
        $this->getChargeTest($obj);
    }
}
