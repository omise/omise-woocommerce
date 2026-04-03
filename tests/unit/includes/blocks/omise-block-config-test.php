<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Block_Config_Test extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        require_once __DIR__ . '/../../../../includes/blocks/omise-block-payments.php';
        require_once __DIR__ . '/../../../../includes/blocks/omise-block-config.php';
    }

    public function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function class_is_initialized()
    {
        $this->expectNotToPerformAssertions();
        $container = \Automattic\WooCommerce\Blocks\Package::container();
        Monkey\Functions\expect('add_action')->andReturn(null);
        new Omise_Block_Config($container);
    }
}
