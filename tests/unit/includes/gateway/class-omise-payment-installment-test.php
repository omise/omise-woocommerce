<?php

use PHPUnit\Framework\TestCase;
use Mockery;

class Omise_Payment_Installment_Test extends TestCase
{
    public function setUp(): void
    {
        // Mocking the parent class
        $offsite = Mockery::mock('overload:Omise_Payment_Offsite');
        $offsite->shouldReceive('init_settings');
        $offsite->shouldReceive('get_option');

        // mocking WP built-in functions
        if (!function_exists('wp_kses')) {
            function wp_kses() {}
        }

        if (!function_exists('add_action')) {
            function add_action() {}
        }

        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-installment.php';
    }

    /**
     * close mockery after tests are done
     */
    public function teardown(): void
    {
        Mockery::close();
    }

    /**
     * @test
     */
    public function getTotalAmountFromAdminOrderpage()
    {
        // mocking built-in WooCommerce function
        if (!function_exists('wc_get_order')) {
            function wc_get_order() {
                $class = new class {
                    public $property;
                
                    public function get_total() { 
                        return 999999;
                    }
                };
                return $class;
            }
        }

        // mocking the WP global variable $wp
        $wp = new stdClass();
        $wp->query_vars = ['order-pay' => 123];
        $GLOBALS['wp'] = $wp;

        $installment = new Omise_Payment_Installment();
        $total = $installment->getTotalAmount();

        $this->assertEquals($total, 999999);
    }

    /**
     * @test
     */
    public function getTotalAmountFromCart()
    {
        // mocking WC() method
        if (!function_exists('WC')) {
            function WC() {
                $class = new stdClass();
                $class->cart = new stdClass();
                $class->cart->total = 999999;
                return $class;
            }
        }

        $installment = new Omise_Payment_Installment();
        $total = $installment->getTotalAmount();

        $this->assertEquals($total, 999999);
    }
}
