<?php

use PHPUnit\Framework\TestCase;

class Charge_Request_Builder_Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../../includes/gateway/traits/charge-request-builder.php';
    }

    /**
     * @test
     */
    public function buildChargeRequestReturnsValidResponse()
    {
        if (!function_exists('get_rest_url')) {
            function get_rest_url() {
                return "http://localhost/";
            }
        }

        $mock = $this->getMockForTrait('Charge_Request_Builder');
        $redirectUrlMock = Mockery::mock('alias:RedirectUrl');
        $redirectUrlMock->shouldReceive('create')
            ->andReturn('https://abc.com/order/complete');
        $redirectUrlMock->shouldReceive('getToken')
            ->andReturn('token123');

        $order_id = 'order_123';
        $order = new class {
            public $property;
        
            public function get_total() { 
                return 999999;
            }

            public function get_currency() {
                return 'THB';
            }

            public function add_meta_data() {}
        };
        $source_type = 'alipay';
        $callback_url = 'omise_alipay_callback';

        $result = $mock->build_charge_request(
            $order_id,
            $order,
            $source_type,
            $callback_url
        );

        $this->assertEquals($source_type, $result['source']['type']);
        $this->assertEquals(999999*100, $result['amount']);
    }
}
