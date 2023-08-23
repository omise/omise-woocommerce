<?php

use PHPunit\Framework\TestCase;
use Mockery;

abstract class Offsite_Test extends TestCase
{
    public function setUp(): void
    {
        // Mocking the parent class
        $offsite = Mockery::mock('overload:Omise_Payment_Offsite');
        $offsite->shouldReceive('init_settings');
        $offsite->shouldReceive('get_option');
        $offsite->shouldReceive('get_provider');

        // mocking WP built-in functions
        if (!function_exists('wp_kses')) {
            function wp_kses() {}
        }

        if (!function_exists('add_action')) {
            function add_action() {}
        }

        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-alipayplus.php';
    }

    /**
     * close mockery after tests are done
     */
    public function teardown(): void
    {
        Mockery::close();
    }
}
