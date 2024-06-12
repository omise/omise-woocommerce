<?php

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

require_once __DIR__ . '/traits/mock-gateways.php';

class Omise_Block_Payment_Test extends TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration, MockPaymentGateways;

    public $obj;

    // @runInSeparateProcess
    protected function setUp() : void
    {
        parent::setUp();
        $this->mockWcGateways();
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/abstract-omise-block-payment.php';
        $this->obj = new class extends Omise_Block_Payment {
            public function set_additional_data() {
                $this->additional_data = [];
            }
        };
    }

    /**
     * @test
     */
    public function initialize()
    {
        Monkey\Functions\expect('get_option')->andReturn(null);

        $reflection = new \ReflectionClass($this->obj);
        $name_property = $reflection->getProperty('name');
        $name_property->setAccessible(true);
        $name_property->setValue($this->obj, 'omise_atome');

        $this->obj->initialize();

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
        Monkey\Functions\expect('wc_string_to_bool')->andReturn(true);
        // Calling initialize() to set $gateway value
        $reflection = new \ReflectionClass($this->obj);
        $name_property = $reflection->getProperty('name');
        $name_property->setAccessible(true);
        $name_property->setValue($this->obj, 'omise_atome');

        $this->obj->initialize();

        $is_active = $this->obj->is_active();
        $this->assertTrue($is_active);
    }

    /**
     * @test
     */
    public function get_payment_method_data()
    {
        Monkey\Functions\expect('is_checkout')->andReturn(true);
        // Calling initialize() to set $gateway value
        $reflection = new \ReflectionClass($this->obj);
        $name_property = $reflection->getProperty('name');
        $name_property->setAccessible(true);
        $name_property->setValue($this->obj, 'omise_atome');

        $this->obj->initialize();

        $data = $this->obj->get_payment_method_data();

        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('supports', $data);
        $this->assertEquals('array', gettype($data['supports']));
        $this->assertEquals('omise_atome', $data['name']);
    }

    /**
     * @test
     */
    public function get_payment_method_script_handles()
    {
        $reflection = new \ReflectionClass($this->obj);
        $name_property = $reflection->getProperty('name');
        $name_property->setAccessible(true);
        $name_property->setValue($this->obj, 'omise_atome');

        Monkey\Functions\expect('wp_script_is')->andReturn(false);
        Monkey\Functions\expect('wp_register_script');
        Monkey\Functions\expect('plugin_dir_url');
        Monkey\Functions\expect('wp_enqueue_script');

        $result = $this->obj->get_payment_method_script_handles();

        $this->assertEquals([ 'wc-omise_atome-payments-blocks' ], $result);
    }
}
