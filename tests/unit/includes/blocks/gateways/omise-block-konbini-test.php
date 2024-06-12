<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Block_Konbini_Test extends TestCase
{
    use MockPaymentGateways;

    public $obj;

    protected function setUp() : void
    {
        parent::setUp();
        $this->mockWcGateways();
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/abstract-omise-block-payment.php';
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/omise-block-konbini.php';
        $this->obj = new Omise_Block_Konbini;
    }

    /**
     * @test
     */
    public function set_additional_data()
    {
        Monkey\Functions\expect('get_option')->andReturn(null);
        $this->obj->initialize();
        $this->obj->set_additional_data();

        $clazz = new \ReflectionClass($this->obj);
        $property = $clazz->getProperty('additional_data');
        $property->setAccessible(true);
        $result = $property->getValue($this->obj);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
