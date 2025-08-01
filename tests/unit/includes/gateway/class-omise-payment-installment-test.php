<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Installment_Test extends Omise_Payment_Offsite_Test
{
    protected $backend_installment_mock;
    private $installment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->installment = $this->mock_payment_class( Omise_Payment_Installment::class );
        $this->backend_installment_mock = Mockery::mock('Omise_Backend_Installment');
    }

    public function test_installment_get_total_amount_from_admin_order_page()
    {
        $order = Mockery::mock('WC_Order');
        Monkey\Functions\expect('wc_get_order')->andReturn($order);
        $order
            ->shouldReceive('get_total')
            ->andReturn(999999);

        // mocking the WP global variable $wp
        $wp = new stdClass();
        $wp->query_vars = ['order-pay' => 123];
        $GLOBALS['wp'] = $wp;

        $total = $this->installment->get_total_amount();

        $this->assertEquals($total, 999999);
    }

    public function test_installment_get_total_amount_from_cart()
    {
        $clazz = new stdClass();
        $clazz->cart = new stdClass();
        $clazz->cart->total = 999999;

        Monkey\Functions\expect('WC')->andReturn($clazz);

        $total = $this->installment->get_total_amount();

        $this->assertEquals($total, 999999);
    }

    public function test_installment_charge()
    {
        putenv('OMISE_CUSTOM_WLB_ORDER_DESC=test');

        $order = $this->getOrderMock(4353, 'THB', [ 'id' => 1293 ]);
        $_POST['omise_source'] = 'source_test_12345';

        $test_charge_fn = function ($actual) {
            return $actual == [
                'amount' => 435300,
                'currency' => 'THB',
                'description' => 'WooCommerce Order id 1293',
                'return_uri' => $this->return_uri,
                'source' => 'source_test_12345',
                'card' => '',
                'metadata' => [
                    'order_id' => 1293,
                ],
            ];
        };

        $this->perform_charge_test( $this->installment, $order, $test_charge_fn );
    }

    public function test_installment_wlb_charge()
    {
        putenv('OMISE_CUSTOM_WLB_ORDER_DESC');

        $order = $this->getOrderMock(250.5, 'THB', [ 'id' => 400 ]);
        $_POST['omise_source'] = 'source_test_12345';
        $_POST['omise_token'] = 'tokn_test_67890';

        $test_charge_fn = function ($actual) {
            return $actual == [
                'amount' => 25050,
                'currency' => 'THB',
                'description' => 'WooCommerce Order id 400',
                'return_uri' => $this->return_uri,
                'source' => 'source_test_12345',
                'card' => 'tokn_test_67890',
                'metadata' => [
                    'order_id' => 400,
                ],
            ];
        };

        $this->perform_charge_test( $this->installment, $order, $test_charge_fn );
    }

    public function test_installment_wlb_charge_with_custom_description()
    {
        putenv('OMISE_CUSTOM_WLB_ORDER_DESC={description} - test');

        $order = $this->getOrderMock(250.5, 'THB', [ 'id' => 400 ]);
        $_POST['omise_source'] = 'source_test_12345';
        $_POST['omise_token'] = 'tokn_test_67890';

        $test_charge_fn = function ($actual) {
            return $actual['description'] == 'WooCommerce Order id 400 - test';
        };

        $this->perform_charge_test( $this->installment, $order, $test_charge_fn );
    }

    public function test_installment_wlb_charge_with_custom_description_fully_overridden()
    {
        putenv('OMISE_CUSTOM_WLB_ORDER_DESC=My order description');

        $order = $this->getOrderMock(250.5, 'THB', [ 'id' => 400 ]);
        $_POST['omise_source'] = 'source_test_12345';
        $_POST['omise_token'] = 'tokn_test_67890';

        $test_charge_fn = function ($actual) {
            return $actual['description'] == 'My order description';
        };

        $this->perform_charge_test( $this->installment, $order, $test_charge_fn );
    }

    public function test_installment_get_view_data()
    {
        $capability = Mockery::mock('alias:Omise_Capability');
        $capability->shouldReceive('retrieve')
            ->andReturn(new class {
                public function getInstallmentMinLimit() {
                    return 2000;
                }

                public function is_zero_interest() {
                    return true;
                }

                public function getInstallmentMethods() {
                    return [];
                }
            });

        $this->backend_installment_mock->shouldReceive('get_available_providers');
        Monkey\Functions\expect('get_woocommerce_currency')->andReturn('thb');

        $cart = $this->getCartMock(['total' => 999999]);
        $wc = $this->getWcMock($cart);
        Monkey\Functions\expect('WC')->andReturn($wc);

        $result = $this->installment->get_view_data();

        $this->assertArrayHasKey('installments_enabled', $result);
        $this->assertArrayHasKey('is_zero_interest', $result);
        $this->assertArrayHasKey('installment_min_limit', $result);
    }

    public function test_installment_get_params_for_js()
    {
        $cart = $this->getCartMock(['total' => 999999]);
        $wc = $this->getWcMock($cart);
        Monkey\Functions\expect('WC')->andReturn($wc);

        $result = $this->installment->getParamsForJS();

        $this->assertEquals([
            'key' => 'pkey_test_123',
            'amount' => 99999900,
        ], $result);
    }

    public function test_installment_convert_to_cents()
    {
        $instance = $this->installment;

        $this->assertEquals(100, $instance->convert_to_cents(1.00));
        $this->assertEquals(150, $instance->convert_to_cents(1.50));
        $this->assertEquals(0, $instance->convert_to_cents(0.00));

        $this->assertEquals(10000, $instance->convert_to_cents(100));
        $this->assertEquals(0, $instance->convert_to_cents(0));

        $this->assertEquals(100, $instance->convert_to_cents('1.00'));
        $this->assertEquals(0, $instance->convert_to_cents('0.00'));
        $this->assertEquals(10000, $instance->convert_to_cents('100'));
    }
}
