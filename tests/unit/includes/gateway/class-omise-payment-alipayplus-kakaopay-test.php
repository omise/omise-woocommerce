<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Kakaopay_Test extends Offsite_Test
{
    public function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-alipayplus.php';
    }

    /**
     * @test
     */
    public function restrictedCountriesHasRequiredCountries()
    {
        $obj = new Omise_Payment_Kakaopay();
        $expectedCountries = ['SG', 'TH'];

        $this->assertEqualsCanonicalizing($expectedCountries, $obj->restricted_countries);
    }
}
