<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Kakaopay_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'kakaopay';
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
