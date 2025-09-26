<?php
namespace Omise\Tests\Helpers;

use Mockery;

trait Omise_WC_Helper {
	public function get_cart_mock( $properties = [] ) {
		$cart = Mockery::spy( 'WC_Cart' );
		$cart->subtotal = $properties['subtotal'] ?? $properties['total'] ?? 0;
		$cart->total = $properties['total'] ?? 0;

		$cart->allows(
			[
				'empty_cart' => null,
			]
		);

		return $cart;
	}

	public function get_wc_mock( $cart = null ) {
		$wc = Mockery::spy( 'WooCommerce' );
		$wc->cart = $cart ? $cart : $this->get_cart_mock();

		return $wc;
	}

	public function get_order_mock( $amount, $currency, $properties = [] ) {
		$order_mock = Mockery::spy( 'WC_Order' );
		$order_mock->allows(
			[
				'get_id' => $properties['id'] ?? 123,
				'get_order_key' => $properties['key'] ?? 'order_kfeERDv',
				'get_currency' => strtoupper( $currency ),
				'get_total' => $amount,
				'get_billing_phone' => '1234567890',
				'get_address' => [
					'country' => 'Thailand',
					'city' => 'Bangkok',
					'postcode' => '10110',
					'state' => 'Bangkok',
					'address_1' => 'Sukumvit Road',
				],
				'get_items' => [
					[
						'name' => 'T Shirt',
						'subtotal' => 600,
						'qty' => 1,
						'product_id' => 'product_123',
						'variation_id' => null,
					],
				],
				'add_meta_data' => null,
				'get_user' => (object) [
					'ID' => 'user_123',
					'test_omise_customer_id' => 'cust_test_123',
				],
				'add_order_note' => null,
				'payment_complete' => null,
			]
		);

		return $order_mock;
	}
}
