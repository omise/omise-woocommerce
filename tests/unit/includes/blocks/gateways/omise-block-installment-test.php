<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

require_once __DIR__ . '/traits/mock-gateways.php';

class Omise_Block_Installment_Test extends TestCase
{
    use MockPaymentGateways;

    public $obj;

    public $omiseSettingMock;

    // @runInSeparateProcess
    protected function setUp() : void
    {
        parent::setUp();
        $this->mockWcGateways();
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/abstract-omise-block-payment.php';
        require_once __DIR__ . '/../../../../../includes/blocks/gateways/omise-block-installment.php';
        $this->obj = new Omise_Block_Installment;
        $this->omiseSettingMock = Mockery::mock('alias:Omise_Setting');
    }

    /**
     * @test
     */
    public function set_additional_data()
    {
        Monkey\Functions\expect('get_option')->andReturn(null);
        $this->omiseSettingMock->shouldReceive('instance')->andReturn($this->omiseSettingMock);
		$this->omiseSettingMock->shouldReceive('public_key')->andReturn('pkey_xxx');

        $this->obj->initialize();
        $this->obj->set_additional_data();

        $clazz = new \ReflectionClass($this->obj);
        $property = $clazz->getProperty('additional_data');
        $property->setAccessible(true);
        $result = $property->getValue($this->obj);

        $this->assertIsArray($result);
        // $this->assertArrayHasKey('is_zero_interest', $result);
        $this->assertArrayHasKey('installment_min_limit', $result);
        $this->assertArrayHasKey('installments_enabled', $result);
        $this->assertArrayHasKey('total_amount', $result);
    }
}
