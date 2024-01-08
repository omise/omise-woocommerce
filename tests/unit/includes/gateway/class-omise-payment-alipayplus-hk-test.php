<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

/**
 * @runTestsInSeparateProcesses
 */
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

    public function testGetChargeRequest()
    {
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderId = 'order_123';
        $obj = new Omise_Payment_Alipay_Hk();
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);
        $result = $obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($this->sourceType, $result['source']['type']);
    }

    public function testCharge()
    {
        $obj = new Omise_Payment_Alipay_Hk();
        $this->getChargeTest($obj);
    }
}
