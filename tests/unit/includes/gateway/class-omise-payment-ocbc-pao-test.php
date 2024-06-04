<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_OCBC_PAO_Test extends Omise_Offsite_Test
{
    private $obj;

    protected function setUp(): void
    {
        $this->sourceType = 'mobile_banking_ocbc';
        parent::setUp();
        Monkey\Functions\expect('add_action');
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-ocbc-pao.php';
        $this->obj = new Omise_Payment_OCBC_PAO();
    }

    /**
     * @test
     */
    public function testCharge()
    {
        Monkey\Functions\expect('wc_get_user_agent')
			->with('123')
			->andReturn('Chrome Web');
        $this->getChargeTest($this->obj);
    }
}
