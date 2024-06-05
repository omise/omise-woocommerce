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
    public function class_is_initialized()
    {
        $this->expectNotToPerformAssertions();
        $container = \Automattic\WooCommerce\Blocks\Package::container();

        Monkey\Functions\expect('add_action')->andReturn(null);

        new Omise_Block_Payments($container);
    }
}
