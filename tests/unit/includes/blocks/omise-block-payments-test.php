<?php

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

class Omise_Block_Payments_Test extends TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

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
    public function class_is_initialized()
    {
        $this->expectNotToPerformAssertions();
        $container = \Automattic\WooCommerce\Blocks\Package::container();

        Monkey\Functions\expect('add_action')->andReturn(null);

        $mock = $this->getMockBuilder(Omise_Block_Payments::class)
            ->setConstructorArgs([$container])
            ->onlyMethods(['add_payment_methods', 'initialize'])
            ->getMock();

        new Omise_Block_Payments($container);
    }
}
