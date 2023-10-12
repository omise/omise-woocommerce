<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Installment_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'installment_ktc';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-installment.php';

        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field() {
                return 'Sanitized text';
            }
        }
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

        unset($GLOBALS['wp']);
        unset($installment);
        unset($wp);
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

    public function testGetChargeRequest()
    {
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

    public function testCharge()
    {
        $_POST['source'] = ['type' => $this->sourceType];
        $_POST[$this->sourceType . '_installment_terms'] = 3;

        $obj = new Omise_Payment_Installment();
        $this->getChargeTest($obj);
    }
}
