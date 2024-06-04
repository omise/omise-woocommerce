<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_TouchNGo_Test extends Omise_Offsite_Test
{
    protected function setUp(): void
    {
        $this->sourceType = 'touch_n_go';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-touch-n-go.php';
    }

    /**
     * @test
     */
    public function restrictedCountriesHasRequiredCountries()
    {
        Monkey\Functions\expect('add_action');
        $obj = new Omise_Payment_TouchNGo();
        $expectedCountries = ['SG', 'MY', 'TH'];

        $this->assertEqualsCanonicalizing($expectedCountries, $obj->restricted_countries);
    }
}
