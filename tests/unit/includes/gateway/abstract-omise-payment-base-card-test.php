<?php

use PHPUnit\Framework\TestCase;

class Omise_Payment_Base_Card_Test extends TestCase
{
    public $obj;

    public function setUp(): void
    {
        $omisePaymentMock = Mockery::mock('overload:Omise_Payment');
        $omisePaymentMock->shouldReceive('is_test')
            ->andReturn(true);

        $omiseCreditCardMock = Mockery::mock('overload:Omise_Payment_Creditcard');
        $omiseCreditCardMock->shouldReceive('get_option')
            ->andReturn(true);

        // Create a mock of the $order object
        $setting = Mockery::mock('overload:Omise_Setting');
        $setting->shouldReceive('instance')
            ->andReturn($setting);
        $setting->shouldReceive('is_dynamic_webhook_enabled')
            ->andReturn(true);

        $redirectUrlMock = Mockery::mock('alias:RedirectUrl');
        $redirectUrlMock->shouldReceive('create')
            ->andReturn('https://abc.com/order/complete');
        $redirectUrlMock->shouldReceive('getToken')
            ->andReturn('token123');

        require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-base-card.php';

        // Create a new instance from the Abstract Class
        $this->obj = new class extends Omise_Payment_Base_Card {
            // Just a sample public function that returns this anonymous instance
            public function returnThis()
            {
                return $this;
            }
        };
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
        $orderMock->shouldReceive('add_meta_data')
            ->andReturn(['order_id' => 'order_123']);
        $orderMock->shouldReceive('get_user')
            ->andReturn((object)[
                'ID' => 'user_123',
                'test_omise_customer_id' => 'cust_test_123'
            ]);
        return $orderMock;
    }

    /**
     * @runInSeparateProcess
     */
    public function testCharge()
    {
        if (!function_exists('wc_clean')) {
            function wc_clean() {
                return 'tokn_123';
            }
        }

        if (!function_exists('get_rest_url')) {
            function get_rest_url() {
                return 'https://abc.com/wp-json/omise/webhooks';
            }
        }

        $expectedAmount = 99999;
        $expectedCurrency = 'thb';
        $expectedChargeResponse = [
            "object" => "charge",
            "id" => "chrg_test_no1t4tnemucod0e51mo",
            "location" => "/charges/chrg_test_no1t4tnemucod0e51mo",
            "amount" => $expectedAmount,
            "currency" => $expectedCurrency
        ];

        $chargeMock = Mockery::mock('overload:OmiseCharge');
        $chargeMock->shouldReceive('create')->once()->andReturn($expectedChargeResponse);

        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['omise_token'] = 'tokn_123';
        $_POST['omise_save_customer_card'] = '';
        $orderId = 'order_123';
        $this->obj->payment_action = 'auto_capture';

        $result = $this->obj->charge(
            $orderId,
            $orderMock
        );

        $this->assertEquals($expectedAmount, $result['amount']);
        $this->assertEquals($expectedCurrency, $result['currency']);
    }
}
