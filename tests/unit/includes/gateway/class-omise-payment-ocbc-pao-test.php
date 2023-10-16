<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_OCBC_PAO_Test extends Omise_Offsite_Test
{
    private $obj;

    public function setUp(): void
    {
        $this->sourceType = 'mobile_banking_ocbc';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-ocbc-pao.php';
        $this->obj = new Omise_Payment_OCBC_PAO();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        // destroy object and clear memory
        unset($this->obj);
    }

    /**
     * @test
     */
    public function testCharge()
    {
        $this->getChargeTest($this->obj);
    }
}
