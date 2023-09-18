<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_OCBC_Digital_Test extends Offsite_Test
{
    private $obj;

    public function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-ocbc-digital.php';
        require_once __DIR__ . '/../../../../includes/classes/class-omise-image.php';
        $this->obj = new Omise_Payment_OCBC_Digital();
    }

    public function tearDown(): void
    {
        // destroy object and clear memory
        unset($this->obj);
    }

    /**
     * @test
     */
    public function restrictedCountriesHasRequiredCountries()
    {
        $expectedCountries = ['SG'];
        $this->assertEqualsCanonicalizing($expectedCountries, $this->obj->restricted_countries);
    }

    /**
     * @test
     */
    public function sourceTypeIsCorrect()
    {
        $this->assertEquals('mobile_banking_ocbc', $this->obj->source_type);
    }

    /**
     * @test
     */
    public function methodTitleIsCorrect()
    {
        $this->assertEquals('Opn Payments OCBC Digital', $this->obj->method_title);
    }

    /**
     * @test
     */
    public function supportsIsCorrect()
    {
        $this->assertEqualsCanonicalizing([ 'products', 'refunds' ], $this->obj->supports);
    }

    /**
     * @test
     */
    public function getIconReturnsCorrectImageLink()
    {
        // mocking WP built-in functions
        if (!function_exists('plugins_url')) {
            function plugins_url() {
                return "http://localhost";
            }
        }

        if (!function_exists('apply_filters')) {
            function apply_filters() {
                return "http://localhost/image.png";
            }
        }

        $result = $this->obj->get_icon();

        $this->assertEquals("http://localhost/image.png", $result);
    }

    /**
     * @test
     */
    public function getChargeRequestReturnsCorrectData()
    {
        $order = new class {
            public function get_currency()
            {
                return 'thb';
            }

            public function get_total()
            {
                return 10000;
            }
        };

        if (!function_exists('wc_get_user_agent')) {
            function wc_get_user_agent() {
                return "Chrome Web";
            }
        }

        $expectedAmount = 1000000;
        $expectedCurrency = 'thb';
        $expectedSourceType = 'mobile_banking_ocbc';
        $order_id = "123";
        $result = $this->obj->get_charge_request($order_id, $order);
        $this->assertEquals($expectedAmount, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
        $this->assertEquals($expectedSourceType, $result['source']['type']);
    }
}
