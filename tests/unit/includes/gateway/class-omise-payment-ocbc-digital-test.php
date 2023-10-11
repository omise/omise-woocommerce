<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_OCBC_Digital_Test extends Omise_Offsite_Test
{
    private $obj;

    public function setUp(): void
    {
        $this->sourceType = 'mobile_banking_ocbc';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-ocbc-digital.php';
        require_once __DIR__ . '/../../../../includes/classes/class-omise-image.php';
        $this->obj = new Omise_Payment_OCBC_Digital();

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

        if (!function_exists('wc_get_user_agent')) {
            function wc_get_user_agent() {
                return "Chrome Web";
            }
        }
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
        $result = $this->obj->get_icon();
        $this->assertEquals("http://localhost/image.png", $result);
    }

    /**
     * @test
     */
    public function testCharge()
    {
        $expectedCurrency = 'SGD';
        $expectedAmount = 1000000; // in subunits
        $expectedAmount = 1000000; // in subunits
        $expectedRequest = [
            "object" => "charge",
            "id" => "chrg_test_no1t4tnemucod0e51mo",
            "location" => "/charges/chrg_test_no1t4tnemucod0e51mo",
            "amount" => $expectedAmount,
            "currency" => $expectedCurrency
        ];

        // Create a mock for OmiseCharge
        $chargeMock = Mockery::mock('overload:OmiseCharge');
        $chargeMock->shouldReceive('create')->once()->andReturn($expectedRequest);

        // Create a mock of the $order object
        $orderMock = Mockery::mock('WC_Order');

        // Define expectations for the mock
        $orderMock->shouldReceive('get_currency')
            ->andReturn($expectedCurrency);
        $orderMock->shouldReceive('get_total')
            ->andReturn($expectedAmount/100);  // in units

        $order_id = "123";
        $result = $this->obj->charge($order_id, $orderMock);
        $this->assertEquals($expectedAmount, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
    }
}
