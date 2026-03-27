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

    private function render_installment_template( $view_data )
    {
        $template_file = __DIR__ . '/../../../../templates/payment/form-installment.php';
        $viewData      = $view_data;

        ob_start();
        include $template_file;
        return ob_get_clean();
    }

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
        define('OMISE_CUSTOM_WLB_ORDER_DESC', 'test');

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
        define('OMISE_CUSTOM_WLB_ORDER_DESC', '{description} - test');

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
        define('OMISE_CUSTOM_WLB_ORDER_DESC', 'My order description');

        $order = $this->getOrderMock(250.5, 'THB', [ 'id' => 400 ]);
        $_POST['omise_source'] = 'source_test_12345';
        $_POST['omise_token'] = 'tokn_test_67890';

        $test_charge_fn = function ($actual) {
            return $actual['description'] == 'My order description';
        };

        $this->perform_charge_test( $this->installment, $order, $test_charge_fn );
    }

    public function test_process_payment_routes_wlb_to_standard_flow()
    {
        $_POST['omise_token'] = 'tokn_test_wlb';

        $installment = new class extends Omise_Payment_Installment {
            public function __construct() {}
            protected function process_standard_payment($order_id) {
                return ['result' => 'standard', 'order_id' => $order_id];
            }
        };

        $result = $installment->process_payment(42);

        $this->assertEquals('standard', $result['result']);
        $this->assertEquals(42, $result['order_id']);

        unset($_POST['omise_token']);
    }

    public function test_process_payment_routes_normal_to_parent_flow()
    {
        unset($_POST['omise_token']);

        $feature_flag = Mockery::mock('alias:Omise_UPA_Feature_Flag');
        $feature_flag->shouldReceive('is_enabled_for_order')->once()->andReturn(true);

        $installment = new class extends Omise_Payment_Installment {
            public function __construct() {}
            protected function should_use_upa_installment_flow() {
                return true;
            }
            public function load_order( $order ) {
                $this->order = (object) [ 'id' => $order ];
                return $this->order;
            }
            protected function process_upa_checkout_session_payment($order_id) {
                return ['result' => 'upa', 'order_id' => $order_id];
            }
        };

        $result = $installment->process_payment(42);

        $this->assertEquals('upa', $result['result']);
        $this->assertEquals(42, $result['order_id']);
    }

    public function test_process_payment_routes_to_standard_flow_when_upa_is_disabled()
    {
        unset($_POST['omise_token']);

        $installment = new class extends Omise_Payment_Installment {
            public function __construct() {}
            protected function should_use_upa_installment_flow() {
                return false;
            }
            protected function process_standard_payment($order_id) {
                return ['result' => 'standard', 'order_id' => $order_id];
            }
        };

        $result = $installment->process_payment(42);

        $this->assertSame('standard', $result['result']);
        $this->assertSame(42, $result['order_id']);
    }

    public function test_process_payment_routes_to_standard_flow_when_upa_disabled_for_order_without_token()
    {
        unset($_POST['omise_token']);

        $feature_flag = Mockery::mock('alias:Omise_UPA_Feature_Flag');
        $feature_flag->shouldReceive('is_enabled_for_order')->once()->andReturn(false);

        $installment = new class extends Omise_Payment_Installment {
            public function __construct() {}
            protected function should_use_upa_installment_flow() {
                return true;
            }
            public function load_order( $order ) {
                $this->order = (object) [ 'id' => $order ];
                return $this->order;
            }
            protected function process_standard_payment($order_id) {
                return ['result' => 'standard', 'order_id' => $order_id];
            }
        };

        $result = $installment->process_payment(42);

        $this->assertSame('standard', $result['result']);
        $this->assertSame(42, $result['order_id']);
    }

    public function test_installment_sets_source_type_for_upa_resolution()
    {
        $this->assertSame('installment', $this->installment->source_type);
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

                public function getWlbInstallmentMethods() {
                    return [];
                }
            });

        $this->backend_installment_mock->shouldReceive('get_available_providers');
        $this->backend_installment_mock->shouldReceive('has_wlb_providers')->andReturn(false);
        Monkey\Functions\expect('get_woocommerce_currency')->andReturn('thb');

        $cart = $this->get_cart_mock(['total' => 999999]);
        $wc = $this->get_wc_mock($cart);
        Monkey\Functions\expect('WC')->andReturn($wc);

        $result = $this->installment->get_view_data();

        $this->assertArrayHasKey('installments_enabled', $result);
        $this->assertArrayHasKey('is_zero_interest', $result);
        $this->assertArrayHasKey('installment_min_limit', $result);
        $this->assertArrayHasKey('has_wlb_providers', $result);
        $this->assertArrayHasKey('is_upa_enabled', $result);
    }

    public function test_installment_get_params_for_js()
    {
        $cart = $this->get_cart_mock(['total' => 999999]);
        $wc = $this->get_wc_mock($cart);
        Monkey\Functions\expect('WC')->andReturn($wc);

        $result = $this->installment->getParamsForJS();

        $this->assertEquals([
            'key' => 'pkey_test_123',
            'amount' => 99999900,
        ], $result);
    }

    public function test_template_renders_form_when_upa_enabled_and_no_wlb_providers()
    {
        Monkey\Functions\expect('get_locale')->once()->andReturn('en_US');

        $output = $this->render_installment_template([
            'is_upa_enabled' => true,
            'has_wlb_providers' => false,
            'installments_enabled' => true,
            'total_amount' => 100000,
            'installment_min_limit' => 2000,
        ]);

        $this->assertStringContainsString('id="omise-installment"', $output);
        $this->assertStringContainsString('window.OMISE_UPDATED_CART_AMOUNT = 100000;', $output);
        $this->assertStringContainsString('window.LOCALE = "en_US";', $output);
    }

    public function test_template_renders_form_when_upa_enabled_and_wlb_providers_available()
    {
        Monkey\Functions\expect('get_locale')->once()->andReturn('en_US');

        $output = $this->render_installment_template([
            'is_upa_enabled' => true,
            'has_wlb_providers' => true,
            'installments_enabled' => true,
            'total_amount' => 100000,
            'installment_min_limit' => 2000,
        ]);

        $this->assertStringContainsString('id="omise-installment"', $output);
    }

    public function test_template_renders_form_when_upa_disabled()
    {
        Monkey\Functions\expect('get_locale')->once()->andReturn('en_US');

        $output = $this->render_installment_template([
            'is_upa_enabled' => false,
            'has_wlb_providers' => false,
            'installments_enabled' => true,
            'total_amount' => 100000,
            'installment_min_limit' => 2000,
        ]);

        $this->assertStringContainsString('id="omise-installment"', $output);
    }

    public function test_template_renders_thb_minimum_message_with_placeholder_value()
    {
        Monkey\Functions\expect('get_woocommerce_currency')->once()->andReturn('THB');

        $output = $this->render_installment_template([
            'installments_enabled' => false,
            'installment_min_limit' => 2000,
        ]);

        $this->assertStringContainsString(
            'There are no installment plans available for this purchase amount (minimum amount is 2000 THB).',
            $output
        );
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
