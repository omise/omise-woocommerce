<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Block_FPX_Test extends TestCase
{
    use MockPaymentGateways;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockWcGateways();
        require_once __DIR__ . '/../../../../../includes/backends/class-omise-backend.php';
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/abstract-omise-block-payment.php';
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/omise-block-fpx.php';
    }

    /**
     * @test
     */
    public function set_additional_data()
    {
        Monkey\Functions\expect('get_option')->andReturn(null);
        $obj = new Omise_Block_Fpx;
        $obj->initialize();
        $obj->set_additional_data();

        $clazz = new \ReflectionClass($obj);
        $property = $clazz->getProperty('additional_data');
        $property->setAccessible(true);
        $result = $property->getValue($obj);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('bank_list', $result);
    }
}