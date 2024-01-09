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
        $this->assertEquals(
            "<img src='/ocbc-digital.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='OCBC Digital' />",
            $result
        );
    }

    /**
     * @test
     */
    public function testCharge()
    {
        Brain\Monkey\Functions\expect('wc_get_user_agent')
			->with('123')
			->andReturn('Chrome Web');
        $this->getChargeTest($this->obj);
    }
}
