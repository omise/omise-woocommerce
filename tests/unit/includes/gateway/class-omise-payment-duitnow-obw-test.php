<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_DuitNow_OBW_Test extends Omise_Offsite_Test
{
    public function setUp(): void
    {
        $this->sourceType = 'duitnow_obw';
        parent::setUp();
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-duitnow-obw.php';

        // if (!function_exists('wp_enqueue_script')) {
        //     function wp_enqueue_script() {}
        // }

        // if (!function_exists('plugins_url')) {
        //     function plugins_url() {}
        // }

        // dummy version
    //     if (!defined('WC_VERSION')) {
    //         define('WC_VERSION', '1.0.0');
    //     }
    }

    public function testCharge()
    {
        $_POST['source'] = ['bank' => 'SCB'];
        $obj = new Omise_Payment_DuitNow_OBW();
        $this->getChargeTest($obj);
    }
}
