<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Mobilebanking_Test extends Omise_Offsite_Test
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/backends/class-omise-backend-mobile-banking.php';
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-mobilebanking.php';
    }

    public function testCharge()
    {
        Monkey\Functions\expect('wc_get_user_agent')
			->with('123')
			->andReturn('Chrome Web');
        Monkey\Functions\expect('wp_kses');
        Monkey\Functions\expect('add_action');
        $_POST['omise-offsite'] = 'mobile_banking_bbl';
        $obj = new Omise_Payment_Mobilebanking();
        $this->getChargeTest($obj);
    }
}
