<?php

use PHPUnit\Framework\TestCase;
use Mockery;

class Omise_Payment_Installment_Test extends TestCase
{
	public function setUp(): void
    {
        $offsite = Mockery::mock('overload:Omise_Payment_Offsite');
        $offsite->shouldReceive('init_settings');
        $offsite->shouldReceive('get_option');

        if (!function_exists('wc_get_order')) {
			function wc_get_order($orderId) {
                $class = new stdClass();
                $class->total = 999999;
                return $class;
			}
		}

		if (!function_exists('WC')) {
			function WC() {
                $class = new stdClass();
                $class->cart = new stdClass();
                $class->cart->total = 999999;
                return $class;
			}
		}

        if (!isset($_GLOBALS['wp'])) {
            $wp = new stdClass();
            $wp->query_vars = ['order-pay' => 11];
        }

        if (!function_exists('wp_kses')) {
            function wp_kses() {}
        }

        if (!function_exists('add_action')) {
            function add_action() {}
        }

		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-installment.php';
	}

    /**
	 * close mockery after test cases are done
	 */
	public function tearDown(): void
	{
		Mockery::close();
	}

    /**
     * @test
     */
    public function getTotalAmount()
    {
        $installment = new Omise_Payment_Installment();
        $total = $installment->getTotalAmount();

        $this->assertEquals($total, 999999);
    }
}
