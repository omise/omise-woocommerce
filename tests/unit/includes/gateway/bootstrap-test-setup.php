<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

// @runInSeparateProcess
abstract class Bootstrap_Test_Setup extends TestCase
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    public $sourceType;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    /**
     * close mockery after tests are done
     */
    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    public function getOrderMock($expectedAmount, $expectedCurrency)
    {
        // Create a mock of the $order object
        $orderMock = Mockery::mock('WC_Order');

        // Define expectations for the mock
        $orderMock->shouldReceive('get_currency')
            ->andReturn($expectedCurrency);
        $orderMock->shouldReceive('get_total')
            ->andReturn($expectedAmount);  // in units
        $orderMock->shouldReceive('add_meta_data');
        $orderMock->shouldReceive('get_billing_phone')
            ->andReturn('1234567890');
        $orderMock->shouldReceive('get_address')
            ->andReturn([
                'country' => 'Thailand',
                'city' => 'Bangkok',
                'postcode' => '10110',
                'state' => 'Bangkok',
                'address_1' => 'Sukumvit Road'
            ]);
        $orderMock->shouldReceive('get_items')
            ->andReturn([
                [
                    'name' => 'T Shirt',
                    'subtotal' => 600,
                    'qty' => 1,
                    'product_id' => 'product_123',
                    'variation_id' => null
                ]
            ]);
        return $orderMock;
    }

    /**
     * @runInSeparateProcess
     */
    public function getChargeTest($classObj)
    {
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $expectedRequest = [
            "object" => "charge",
            "id" => "chrg_test_no1t4tnemucod0e51mo",
            "location" => "/charges/chrg_test_no1t4tnemucod0e51mo",
            "amount" => $expectedAmount,
            "currency" => $expectedCurrency
        ];

        // Create a mock for OmiseCharge
        $chargeMock = Mockery::mock('overload:OmiseCharge');
        $chargeMock->shouldReceive('create')->once()->andReturn($expectedRequest);

        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $wcProduct = Mockery::mock('overload:WC_Product');
        $wcProduct->shouldReceive('get_sku')
            ->once()
            ->andReturn('sku_1234');

        $orderId = 'order_123';
        $result = $classObj->charge($orderId, $orderMock);
        $this->assertEquals($expectedAmount, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
    }
}

