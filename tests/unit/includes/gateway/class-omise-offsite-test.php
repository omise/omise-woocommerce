<?php

use PHPUnit\Framework\TestCase;

abstract class Omise_Offsite_Test extends TestCase
{
    public $sourceType;

    public function setUp(): void
    {
        // Mocking the parent class
        $offsite = Mockery::mock('overload:Omise_Payment_Offsite');
        $offsite->shouldReceive('init_settings');
        $offsite->shouldReceive('get_option');
        $offsite->shouldReceive('get_provider');
        $offsite->shouldReceive('build_charge_request')
            ->andReturn([
                'source' => [ 'type' => $this->sourceType ]
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

    public function getOrderMock($expectedAmount, $expectedCurrency)
    {
        // Create a mock of the $order object
        $orderMock = Mockery::mock('WC_Order');

        // Define expectations for the mock
        $orderMock->shouldReceive('get_currency')
            ->andReturn($expectedCurrency);
        $orderMock->shouldReceive('get_total')
            ->andReturn($expectedAmount);  // in units
        $orderMock->shouldReceive('add_meta_data');
        $orderMock->shouldReceive('get_billing_phone')
            ->andReturn('1234567890');
        $orderMock->shouldReceive('get_address')
            ->andReturn([
                'country' => 'Thailand',
                'city' => 'Bangkok',
                'postcode' => '10110',
                'state' => 'Bangkok',
                'address_1' => 'Sukumvit Road'
            ]);
        $orderMock->shouldReceive('get_items')
            ->andReturn([
                [
                    'name' => 'T Shirt',
                    'subtotal' => 600,
                    'qty' => 1,
                    'product_id' => 'product_123',
                    'variation_id' => null
                ]
            ]);
        return $orderMock;
    }

    /**
     * close mockery after tests are done
     */
    public function tearDown(): void
    {
        Mockery::close();
    }
}
