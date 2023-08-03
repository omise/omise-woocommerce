<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../class-omise-unit-test.php';

class Omise_Payment_Installment_Test extends TestCase
{
	public static function setUpBeforeClass(): void
    {
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
			}
		}

        if (!isset($_GLOBALS['wp'])) {
            $wp = new stdClass();
            $wp->query_vars = ['order-pay' => 11];
        }

		require_once __DIR__ . '/../../../includes/gateway/class-omise-payment-installation.php';
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
