<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_OCBC_Digital_Test extends Offsite_Test
{
    private $obj;

    public function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-ocbc-digital.php';
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

    public function supportsIsCorrect()
    {
        $this->assertEqualsCanonicalizing([ 'products', 'refunds' ], $this->obj->supports);
    }
}
