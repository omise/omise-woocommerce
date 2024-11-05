<?php

use Brain\Monkey;

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_DuitNow_OBW_Test extends Omise_Offsite_Test
{
    private $omise_capability_mock;

    public function setUp(): void
    {
        $this->sourceType = 'duitnow_obw';
        parent::setUp();
        Monkey\Functions\expect('add_action');
        $this->omise_capability_mock = Mockery::mock('alias:Omise_Capabilities');
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-duitnow-obw.php';
    }

    public function testCharge()
    {
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field() {
                return 'Sanitized text';
            }
        }

        $this->omise_capability_mock->shouldReceive('retrieve')->once();
        $_POST['source'] = ['bank' => 'SCB'];
        $obj = new Omise_Payment_DuitNow_OBW();
        $this->getChargeTest($obj);
    }
}
