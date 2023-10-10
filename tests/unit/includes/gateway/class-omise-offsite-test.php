<?php

use PHPUnit\Framework\TestCase;

abstract class Omise_Offsite_Test extends TestCase
{
    public function setUp(): void
    {
        // Mocking the parent class
        $offsite = Mockery::mock('overload:Omise_Payment_Offsite');
        $offsite->shouldReceive('init_settings');
        $offsite->shouldReceive('get_option');
        $offsite->shouldReceive('get_provider');
        $offsite->shouldReceive('build_charge_request')
            ->andReturn([
                'source' => [ 'type' => 'source_123' ]
            ]);

        // mocking WP built-in functions
        if (!function_exists('wp_kses')) {
            function wp_kses() {}
        }

        if (!function_exists('add_action')) {
            function add_action() {}
        }

        // destroy object and clear memory
        unset($offsite);
    }

    /**
     * close mockery after tests are done
     */
    public function tearDown(): void
    {
        Mockery::close();
    }
}
