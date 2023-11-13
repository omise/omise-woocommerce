<?php

class Omise_Payment_Konbini_Test extends Omise_Payment_Offline_Test
{
    public $expectedAmount = 999999;
    public $expectedCurrency = 'thb';

    public function setUp(): void
    {
        parent::setUp();
        // Mocking the parent class
        $offline = Mockery::mock('overload:Omise_Payment_Offline');
        $offline->shouldReceive('init_settings');
        $offline->shouldReceive('get_option');
        $offline->shouldReceive('get_provider');
        $offline->shouldReceive('build_charge_request')
            ->andReturn([
                'amount' => $this->expectedAmount,
                'currency' => $this->expectedCurrency,
                'source' => [ 'type' => 'econtext' ]
            ]);

        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-konbini.php';

        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field() {
                return 'Sanitized text';
            }
        }
    }

    public function testGetChargeRequest()
    {
        $obj = new Omise_Payment_Konbini();

        $orderId = 'order_123';
        $orderMock = $this->getOrderMock(
            $this->expectedAmount,
            $this->expectedCurrency
        );

        $_POST['omise_konbini_name'] = 'Sanitized text';
        $_POST['omise_konbini_email'] = 'omsie@opn.ooo';
        $_POST['omise_konbini_phone'] = '1234567890';

        $result = $obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($this->expectedAmount, $result['amount']);
        $this->assertEquals($this->expectedCurrency, $result['currency']);
        $this->assertEquals(
            $_POST['omise_konbini_name'],
            $result['source']['name']
        );

        unset($_POST['omise_konbini_name']);
        unset($_POST['omise_konbini_email']);
        unset($_POST['omise_konbini_phone']);
        unset($obj);
    }

    public function testCharge()
    {
        $_POST['omise_konbini_name'] = 'Sanitized text';
        $_POST['omise_konbini_email'] = 'omsie@opn.ooo';
        $_POST['omise_konbini_phone'] = '1234567890';

        $obj = new Omise_Payment_Konbini();
        $this->getChargeTest($obj);
    }
}
