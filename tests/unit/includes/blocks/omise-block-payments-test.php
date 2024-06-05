<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Block_Payments_Test extends TestCase
{
    public $obj;

    // @runInSeparateProcess
    protected function setUp() : void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/blocks/omise-block-payments.php';
    }

    /**
     * @test
     */
    public function register_payment_methods_registers_block_classes()
    {
        $this->expectNotToPerformAssertions();
        $container = \Automattic\WooCommerce\Blocks\Package::container();

        Monkey\Functions\expect('add_action')->andReturn(null);

        $obj = new Omise_Block_Payments($container);

        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty('payment_methods');
        $payment_methods = $property->getValue($obj);

        $mock = new class {
            public function register($class) { }
        };

        foreach($payment_methods as $payment_method) {
            $this->getMockBuilder($payment_method)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $obj->register_payment_methods($mock);
    }
}
