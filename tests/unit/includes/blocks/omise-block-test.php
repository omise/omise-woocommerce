<?php

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;

class Omise_Block_Test extends TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    public $obj;

    // @runInSeparateProcess
    protected function setUp() : void
    {
        parent::setUp();
        Monkey\setUp();
        Monkey\Functions\stubs(
            [
                'WC' => (object) [
                    'version' => '6.8.9',
                ],
            ]
        );
        require_once __DIR__ . '/../../../../includes/blocks/omise-block.php';
        $this->obj = new Omise_Block;
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
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
