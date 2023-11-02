<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Alipay_Hk_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'alipay_hk';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-alipayplus.php';

        if (!function_exists('wc_get_user_agent')) {
            function wc_get_user_agent() {
                return "Chrome Web";
            }
        }
    }

    /**
     * @test
     */
    public function restrictedCountriesHasRequiredCountries()
    {
        $obj = new Omise_Payment_Alipay_Hk();
        $expectedCountries = ['SG', 'TH'];

        $this->assertEqualsCanonicalizing($expectedCountries, $obj->restricted_countries);
        unset($expectedCountries);
        unset($obj);
    }

    public function testCharge()
    {
        $obj = new Omise_Payment_Alipay_Hk();
        $this->getChargeTest($obj);
    }
}
