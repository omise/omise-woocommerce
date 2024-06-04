<?php

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class Omise_Block_Test extends TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    public $obj;

    // @runInSeparateProcess
    protected function setUp() : void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/blocks/omise-block.php';
        $this->obj = new Omise_Block;
    }

    /**
     * @test
     */
    public function container()
    {
        $result = $this->obj::container();
        $this->assertTrue(isset($result));
    }

    /**
     *@test
     */
    public function is_active_returns_false()
    {
        $clazz = new \ReflectionClass($this->obj);
        $property = $clazz->getMethod('is_active');
        $property->setAccessible(true);
        $result = $property->invoke($clazz);

        $this->assertFalse($result);
    }
}
