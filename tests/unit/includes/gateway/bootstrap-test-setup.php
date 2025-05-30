<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Mock abstract WooCommerce's gateway
 */
abstract class WC_Payment_Gateway
{
    public static $is_available = true;

    public function is_available()
    {
        return self::$is_available;
    }
}

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

    protected function mockOmiseHttpExecutor()
    {
        require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/OmiseCapability.php';
        require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/OmiseCharge.php';

        return Mockery::mock('overload:' . OmiseHttpExecutor::class);
    }

    protected function mockOmiseSetting($pkey, $skey)
    {
        $omiseSettingMock = Mockery::mock('overload:Omise_Setting');

        $omiseSettingMock->shouldReceive('instance')->andReturn($omiseSettingMock);
        $omiseSettingMock->shouldReceive('public_key')->andReturn($pkey);
        $omiseSettingMock->shouldReceive('secret_key')->andReturn($skey);

        return $omiseSettingMock;
    }

    protected function mockApiCall($fixture, $customAttrs = null)
    {
        $this->mockOmiseSetting(pkey: ['pkey_xxx'], skey: ['skey_xxx']);
        $this->enableApiCall(true);

        $response = load_fixture($fixture);

        if ($customAttrs) {
            $responseAttrs = json_decode($response, true);
            $responseAttrs = array_replace_recursive($responseAttrs, $customAttrs);

            $response = json_encode($responseAttrs);
        }

        // error_log($response);
        $omiseHttpExecutorMock = $this->mockOmiseHttpExecutor();
        $omiseHttpExecutorMock
            ->shouldReceive('execute')
            ->once()
            ->andReturn($response);
    }

    protected function enableApiCall($isEnabled)
    {
        if (!$isEnabled) {
            Brain\Monkey\Functions\expect('is_admin')->andReturn(false);
            Brain\Monkey\Functions\expect('is_checkout')->andReturn(false);
            Brain\Monkey\Functions\expect('is_wc_endpoint_url')->andReturn(false);
        } else {
            Brain\Monkey\Functions\expect('is_admin')->andReturn(false);
            Brain\Monkey\Functions\expect('is_checkout')->andReturn(true);
            Brain\Monkey\Functions\expect('is_wc_endpoint_url')->andReturn(false);
        }
    }
}

