<?php

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

require_once __DIR__ . '/traits/mock-gateways.php';

class Omise_Block_Credit_Card_Test extends TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration, MockPaymentGateways;

    public $obj;

    // @runInSeparateProcess
    protected function setUp() : void
    {
        parent::setUp();
        $this->mockWcGateways();
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/omise-block-credit-card.php';
        $this->obj = new Omise_Block_Credit_Card;
    }

    /**
     * @test
     */
    public function initialize()
    {
        Monkey\Functions\expect('get_option')->andReturn(null);

        $this->obj->initialize();

        $reflection = new \ReflectionClass($this->obj);
        $gateway_property = $reflection->getProperty('gateway');
        $gateway_property->setAccessible(true);
        $gateway_val = $gateway_property->getValue($this->obj);

        $this->assertEquals('object', gettype($gateway_val));
    }

    /**
     * @test
     */ 
    public function is_active()
    {
        $this->obj->initialize();

        $is_active = $this->obj->is_active();
        $this->assertTrue($is_active);
    }

    /**
     */
    public function get_payment_method_data()
    {
        // Calling initialize() to set $gateway value
        $reflection = new \ReflectionClass($this->obj);
        $name_property = $reflection->getProperty('name');
        $name_property->setAccessible(true);
        $name_property->setValue($this->obj, 'abc');

        $this->obj->initialize();

        $data = $this->obj->get_payment_method_data();

        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('supports', $data);
        $this->assertEquals('array', gettype($data['supports']));
        $this->assertEquals('abc', $data['name']);
    }

    /**
     * @test
     */
    public function get_payment_method_script_handles()
    {
        Monkey\Functions\expect('wp_script_is')->andReturn(false);
        Monkey\Functions\expect('wp_register_script');
        Monkey\Functions\expect('plugin_dir_url');
        Monkey\Functions\expect('is_checkout')->andReturn(true);
        // Monkey\Functions\expect('is_user_logged_in')->andReturn(false);

        $this->obj->initialize();

        $result = $this->obj->get_payment_method_script_handles();

        $this->assertEquals([ 'omise-payments-blocks' ], $result);
    }
}
