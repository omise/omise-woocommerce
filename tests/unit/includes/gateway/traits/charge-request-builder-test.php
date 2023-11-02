<?php

use PHPUnit\Framework\TestCase;

class Charge_Request_Builder_Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../includes/gateway/traits/charge-request-builder-trait.php';

        if (!function_exists('get_rest_url')) {
            function get_rest_url() {
                return "http://localhost/";
            }
        }
    }

    /**
     * close mockery after tests are done
     */
    public function tearDown(): void
    {
        Mockery::close();
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
        return $orderMock;
    }

    public function testBuildChargeRequestForNonOfflinePayment()
    {
        $redirectUrlMock = Mockery::mock('alias:RedirectUrl');
        $redirectUrlMock->shouldReceive('create')
            ->andReturn('https://abc.com/order/complete');
        $redirectUrlMock->shouldReceive('getToken')
            ->andReturn('token123');

        $order_id = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $source_type = 'alipay';
        $callback_url = 'omise_alipay_callback';

        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        // Create a mock of the $order object
        $setting = Mockery::mock('alias:Omise_Setting')->makePartial();

        $setting->shouldReceive('is_dynamic_webhook_enabled')
            ->andReturn(true);

        // Define expectations for the mock
        $setting->shouldReceive('instance')
            ->andReturn($setting);

        $mock = $this->getMockForTrait('Charge_Request_Builder');
        $result = $mock->build_charge_request(
            $order_id,
            $orderMock,
            $source_type,
            $callback_url
        );

        $this->assertEquals($source_type, $result['source']['type']);
        $this->assertEquals($expectedAmount*100, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
    }

    public function testBuildChargeRequestForOfflinePayment()
    {
        $order_id = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';

        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        // Create a mock of the $order object
        $setting = Mockery::mock('alias:Omise_Setting')->makePartial();

        $setting->shouldReceive('is_dynamic_webhook_enabled')
            ->shouldReceive(1);

        // Define expectations for the mock
        $setting->shouldReceive('instance')
            ->andReturn($setting);

        $source_type = 'promptpay';
        $mock = $this->getMockForTrait('Charge_Request_Builder');
        $result = $mock->build_charge_request(
            $order_id,
            $orderMock,
            $source_type,
            null,// null means payment is offline
        );

        $this->assertEquals($source_type, $result['source']['type']);
        $this->assertEquals($expectedAmount*100, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
    }

    /**
     * @dataProvider sourceTypeDataProvider
     */
    public function testBuildChargeRequestAddsPlatformTypeForSomeSource($source_type)
    {
        $redirectUrlMock = Mockery::mock('alias:RedirectUrl');
        $redirectUrlMock->shouldReceive('create')
            ->andReturn('https://abc.com/order/complete');
        $redirectUrlMock->shouldReceive('getToken')
            ->andReturn('token123');

        $order_id = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $callback_url = 'omise_' . $source_type . '_callback';

        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        // Create a mock of the $order object
        $setting = Mockery::mock('alias:Omise_Setting')->makePartial();

        $setting->shouldReceive('is_dynamic_webhook_enabled')
            ->andReturn(true);

        // Define expectations for the mock
        $setting->shouldReceive('instance')
            ->andReturn($setting);

        $mock = $this->getMockForTrait('Charge_Request_Builder');
        $result = $mock->build_charge_request(
            $order_id,
            $orderMock,
            $source_type,
            $callback_url
        );

        $this->assertEquals($source_type, $result['source']['type']);
        $this->assertEquals($expectedAmount*100, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
        $this->assertArrayHasKey('platform_type', $result['source']);
    }

    public static function sourceTypeDataProvider() {
        return [
            ['mobile_banking_kbank'],
            ['mobile_banking_scb'],
            ['mobile_banking_bay'],
            ['mobile_banking_bbl'],
            ['mobile_banking_ktb'],
            ['mobile_banking_ocbc_pao'],
            ['mobile_banking_ocbc'],
            ['installment_first_choice'],
            ['installment_bay'],
            ['installment_bbl'],
            ['installment_kbank'],
            ['installment_ktc'],
            ['installment_scb'],
            ['installment_citi'],
            ['installment_ttb'],
            ['installment_uob'],
            ['installment_mbb'],
            ['alipay_cn'],
            ['alipay_hk'],
            ['dana'],
            ['gcash'],
            ['kakaopay'],
        ];
    }
}
