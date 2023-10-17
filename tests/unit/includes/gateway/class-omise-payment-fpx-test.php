<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_FPX_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'fpx';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/backends/class-omise-backend-fpx.php';
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-fpx.php';
    }

    public function testCharge()
    {
        if (!function_exists('sanitize_text_field')) {
            function sanitize_text_field() {
                return 'Sanitized text';
            }
        }

        $_POST['source'] = ['bank' => 'SCB'];
        $obj = new Omise_Payment_FPX();
        $this->getChargeTest($obj);
    }
}
