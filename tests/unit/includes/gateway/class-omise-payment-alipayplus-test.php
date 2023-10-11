<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Alipay_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'alipay_hk';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-alipayplus.php';
    }
}
