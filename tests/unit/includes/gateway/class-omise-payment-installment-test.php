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
        if (!function_exists('wc_clean')) {
            function wc_clean() {
                return 'src_test_123';
            }
        }
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderId = 'order_123';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['source'] = ['type' => $this->sourceType];
        $_POST[$this->sourceType . '_installment_terms'] = 3;
        $_POST['omise_source'] = 'src_test_123';
        $installment = new Omise_Payment_Installment();
        $result = $installment->get_charge_request($orderId, $orderMock);

        $this->assertEquals('src_test_123', $result['source']);
    }

    public function testCharge()
    {
        $_POST['source'] = ['type' => $this->sourceType];
        $_POST[$this->sourceType . '_installment_terms'] = 3;

        $obj = new Omise_Payment_Installment();
        $this->getChargeTest($obj);
    }

    public function testGetParamsForJS()
    {
        $mock = Mockery::mock('overload:Omise_Payment_Offsite');
        $instance = new Omise_Payment_Installment($mock);
        $result = $instance->getParamsForJS();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertEquals('pkey_test_123', $result['key']);
        $this->assertEquals(99999900, $result['amount']);
    }

    public function testConvertToCents()
    {
        $instance = new Omise_Payment_Installment();
        $this->assertEquals(100, $instance->convertToCents(1.00));
        $this->assertEquals(150, $instance->convertToCents(1.50));
        $this->assertEquals(0, $instance->convertToCents(0.00));

        $this->assertEquals(10000, $instance->convertToCents(100));
        $this->assertEquals(0, $instance->convertToCents(0));

        $this->assertEquals(100, $instance->convertToCents('1.00'));
        $this->assertEquals(0, $instance->convertToCents('0.00'));
        $this->assertEquals(10000, $instance->convertToCents('100'));
    }
}
