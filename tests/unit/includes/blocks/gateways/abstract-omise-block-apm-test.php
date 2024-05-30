<?php

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

class Omise_Block_Apm_Test extends TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    public $obj;

    // @runInSeparateProcess
    protected function setUp() : void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/abstract-omise-block-apm.php';
        $this->obj = new class extends Omise_Block_Apm {};
    }

    /**
     * @test
     */
    public function initialize()
    {
        $clazz = (object) [
            'payment_gateways' => new class {
                function payment_gateways() {
                    // dummy gateway
                    $gateway = new class {
                        public $supports = ['products'];

                        public function is_available() {
                            return true;
                        }

                        public function supports() {
                            return $this->supports;
                        }
                    };

                    return [
                        'abc' => $gateway,
                        'xyz' => $gateway,
                    ];
                }
            }
        ];

        Monkey\Functions\expect('get_option')->andReturn(null);
        Monkey\Functions\expect('WC')->andReturn($clazz);

        $reflection = new \ReflectionClass($this->obj);
        $name_property = $reflection->getProperty('name');
        $name_property->setAccessible(true);
        $name_property->setValue($this->obj, 'abc');

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
        // Calling initialize() to set $gateway value
        $reflection = new \ReflectionClass($this->obj);
        $name_property = $reflection->getProperty('name');
        $name_property->setAccessible(true);
        $name_property->setValue($this->obj, 'abc');

        $this->obj->initialize();

        $is_active = $this->obj->is_active();
        $this->assertTrue($is_active);
    }

    /**
     * @test
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
        Monkey\Functions\expect('wp_enqueue_script');

        $result = $this->obj->get_payment_method_script_handles();

        $this->assertEquals([ 'wc-omise-one-click-apms-payments-blocks' ], $result);
    }
}
