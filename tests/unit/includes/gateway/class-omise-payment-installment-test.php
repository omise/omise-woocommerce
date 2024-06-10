<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

use Brain\Monkey;

/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */

class Omise_Payment_Installment_Test extends Omise_Offsite_Test
{
    protected $backend_installment_mock;

    protected function setUp(): void
    {
        $this->sourceType = 'installment_ktc';
        parent::setUp();

        Monkey\Functions\expect('wp_kses');
        Omise_Unit_Test::include_class('backends/class-omise-backend.php');
		Omise_Unit_Test::include_class('backends/class-omise-backend-installment.php');

        $this->backend_installment_mock = Mockery::mock('Omise_Backend_Installment');
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-installment.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function get_total_amount_from_admin_order_page()
    {
        // mocking built-in WooCommerce function
        if (!function_exists('wc_get_order')) {
            function wc_get_order() {
                $class = new class {
                    public function get_total() { 
                        return 999999;
                    }
                };
                return $class;
            }
        }

        Monkey\Functions\expect('add_action');


        // mocking the WP global variable $wp
        $wp = new stdClass();
        $wp->query_vars = ['order-pay' => 123];
        $GLOBALS['wp'] = $wp;

        $installment = new Omise_Payment_Installment();
        $total = $installment->get_total_amount();

        $this->assertEquals($total, 999999);
    }

    /**
     * @test
     */
    public function get_total_amount_from_cart()
    {
        Monkey\Functions\expect('add_action');

        $clazz = new stdClass();
        $clazz->cart = new stdClass();
        $clazz->cart->total = 999999;

        Monkey\Functions\expect('WC')->andReturn($clazz);

        $installment = new Omise_Payment_Installment();
        $total = $installment->get_total_amount();

        $this->assertEquals($total, 999999);
    }

    public function test_get_charge_request()
    {
        $this->backend_installment_mock->shouldReceive('get_provider');

        Monkey\Functions\expect('add_action');

        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderId = 'order_123';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['source'] = ['type' => $this->sourceType];
        $_POST[$this->sourceType . '_installment_terms'] = 3;

        $installment = new Omise_Payment_Installment();
        $result = $installment->get_charge_request($orderId, $orderMock);

        $this->assertEquals($this->sourceType, $result['source']['type']);
    }

    public function test_charge()
    {
        $this->backend_installment_mock->shouldReceive('get_provider');

        Monkey\Functions\expect('add_action');

        $_POST['source'] = ['type' => $this->sourceType];
        $_POST[$this->sourceType . '_installment_terms'] = 3;

        $obj = new Omise_Payment_Installment();
        $this->getChargeTest($obj);
    }

    public function test_get_view_data()
    {
        $capability = Mockery::mock('alias:Omise_Capabilities');
        $capability->shouldReceive('retrieve')
            ->andReturn(new class {
                public function getInstallmentMinLimit() {
                    return 2000;
                }

                public function is_zero_interest() {
                    return true;
                }

                public function getInstallmentBackends() {
                    return [];
                }
            });

        $this->backend_installment_mock->shouldReceive('get_available_providers');
        Monkey\Functions\expect('get_woocommerce_currency')->andReturn('thb');

        $clazz = new stdClass();
        $clazz->cart = new stdClass();
        $clazz->cart->total = 999999;

        Monkey\Functions\expect('WC')->andReturn($clazz);
        Monkey\Functions\expect('add_action');

        $obj = new Omise_Payment_Installment();
        $result = $obj->get_view_data();

        $this->assertArrayHasKey('installment_backends', $result);
        $this->assertArrayHasKey('is_zero_interest', $result);
        $this->assertArrayHasKey('installment_min_limit', $result);
    }
}
