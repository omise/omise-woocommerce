<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Base_Card_Test extends Omise_Test_Case {
	private $base_charge = [
		'object' => 'charge',
		'id' => 'chrg_test_no1t4tnemucod0e51mo',
		'amount' => 1000,
		'currency' => 'THB',
		'status' => 'pending',
		'paid' => false,
		'failure_code' => null,
		'failure_message' => null,
	];

	protected function setUp(): void {
		parent::setUp();

		Monkey\Functions\stubs(
			[
				'add_action',
				'add_filter',
				'do_action',
				'esc_url' => null,
				'wc_clean' => null,
				'wc_string_to_bool' => function ( $val ) {
					return $val === 'yes' || $val === '1';
				},
				'wp_kses' => null,
			]
		);

		require_once __DIR__ . '/../../../../includes/libraries/omise-plugin/helpers/class-omise-wc-order-note.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment.php';
		require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-base-card.php';

		$redirect_url_mock = Mockery::mock( 'alias:RedirectUrl' );
		$redirect_url_mock->shouldReceive( 'create' )
			->andReturn( 'https://abc.com/order/complete' );
		$redirect_url_mock->shouldReceive( 'getToken' )
			->andReturn( 'token123' );

		$setting = $this->mock_omise_setting( 'pkey_test_123', 'skey_test_123' );
		$setting->shouldReceive( 'is_test' )->andReturn( true );
		load_plugin();
	}

	private function new_instance( $settings = [] ) {
		return new class($settings) extends Omise_Payment_Base_Card {
			private $settings;

			public function __construct( $settings ) {
				parent::__construct();
				$this->settings = $settings;
			}

			public function get_option( $key, $default = null ) {
				return $this->settings[ $key ] ?? $default;
			}

			public function get_return_url( $order ) {
				return 'https://abc.com/order/thank-you';
			}
		};
	}

	public function test_base_card_charge() {
		$expected_amount = 99999;
		$expected_currency = 'thb';
		$expected_charge = [
			'object' => 'charge',
			'id' => 'chrg_test_no1t4tnemucod0e51mo',
			'location' => '/charges/chrg_test_no1t4tnemucod0e51mo',
			'amount' => $expected_amount,
			'currency' => $expected_currency,
		];
		$charge_mock = Mockery::mock( 'overload:OmiseCharge' );
		$charge_mock->shouldReceive( 'create' )->once()->andReturn( $expected_charge );
		$order_mock = $this->get_order_mock( $expected_amount, $expected_currency );

		$_POST['omise_token'] = 'tokn_123';
		$_POST['omise_save_customer_card'] = '';

		$klass = $this->new_instance();
		$klass->payment_action = 'auto_capture';
		$result = $klass->charge( $order_mock->get_id(), $order_mock );

		$charge_mock->shouldHaveReceived( 'create' )->once()->with(
			[
				'amount' => 9999900,
				'currency' => 'THB',
				'description' => 'WooCommerce Order id 123',
				'return_uri' => 'https://abc.com/order/complete',
				'metadata' => [ 'order_id' => 123 ],
				'card' => 'tokn_123',
				'capture' => true,
			]
		);
		$this->assertEquals( 'charge', $result['object'] );
		$this->assertEquals( 'chrg_test_no1t4tnemucod0e51mo', $result['id'] );
	}

	public function test_base_card_result_with_paid_charge() {
		$order = $this->get_order_mock( 100, 'thb' );
		$cart = $this->get_cart_mock();
		$wc = $this->get_wc_mock( $cart );
		Monkey\Functions\expect( 'WC' )->andReturn( $wc );

		$charge = array_merge(
			$this->base_charge, [
				'status' => 'successful',
				'authorized' => true,
				'paid' => true,
			]
		);

		$klass = $this->new_instance();
		$klass->payment_action = 'auto_capture';
		$result = $klass->result( $order->get_id(), $order, $charge );

		$order->shouldHaveReceived( 'add_order_note' )->once()->with(
			'Omise: Payment successful.<br/>An amount of 100 THB has been paid'
		);
		$order->shouldNotHaveReceived( 'update_meta_data' );
		$order->shouldHaveReceived( 'payment_complete' )->once();
		$cart->shouldHaveReceived( 'empty_cart' )->once();

		$this->assertEquals(
			[
				'result'   => 'success',
				'redirect' => 'https://abc.com/order/thank-you',
			], $result
		);
	}

	public function test_base_card_result_with_authorized_charge() {
		$order = $this->get_order_mock( 100, 'thb' );
		$cart = $this->get_cart_mock();
		$wc = $this->get_wc_mock( $cart );
		Monkey\Functions\expect( 'WC' )->andReturn( $wc );

		$charge = array_merge(
			$this->base_charge, [
				'status' => 'pending',
				'authorized' => true,
				'paid' => false,
			]
		);

		$klass = $this->new_instance();
		$klass->payment_action = 'manual_capture';
		$result = $klass->result( $order->get_id(), $order, $charge );

		$order->shouldHaveReceived( 'add_order_note' )->once()->with(
			'Omise: Payment processing.<br/>An amount of 100 THB has been authorized'
		);
		$order->shouldHaveReceived( 'update_meta_data' )
			->once()
			->with( 'is_awaiting_capture', 'yes' );
		$order->shouldHaveReceived( 'payment_complete' )->once();
		$cart->shouldHaveReceived( 'empty_cart' )->once();

		$this->assertEquals(
			[
				'result'   => 'success',
				'redirect' => 'https://abc.com/order/thank-you',
			], $result
		);
	}

	public function test_base_card_result_with_unauthorized_3ds_charge() {
		$order = $this->get_order_mock( 100, 'thb' );
		Monkey\Functions\expect( 'WC' )->never();

		$charge = array_merge(
			$this->base_charge, [
				'status' => 'pending',
				'authorized' => false,
				'authorize_uri' => 'https://omise.co/3ds/authenticate',
				'paid' => false,
			]
		);

		$klass = $this->new_instance();
		$klass->payment_action = 'auto_capture';
		$result = $klass->result( $order->get_id(), $order, $charge );

		$order->shouldHaveReceived( 'add_order_note' )->once()->with(
			'Omise: Processing a 3-D Secure payment, redirecting buyer to https://omise.co/3ds/authenticate'
		);
		$order->shouldNotHaveReceived( 'update_meta_data' );
		$order->shouldNotHaveReceived( 'payment_complete' );

		$this->assertEquals(
			[
				'result'   => 'success',
				'redirect' => 'https://omise.co/3ds/authenticate',
			], $result
		);
	}

	public function test_base_card_result_with_unauthorized_passkey_charge() {
		$order = $this->get_order_mock( 100, 'thb' );
		Monkey\Functions\expect( 'WC' )->never();

		$charge = array_merge(
			$this->base_charge, [
				'status' => 'pending',
				'authorized' => false,
				'authorize_uri' => 'https://omise.co/passkey/authenticate',
				'authenticated_by' => 'PASSKEY',
				'paid' => false,
			]
		);

		$klass = $this->new_instance();
		$klass->payment_action = 'auto_capture';
		$result = $klass->result( $order->get_id(), $order, $charge );

		$order->shouldHaveReceived( 'add_order_note' )->once()->with(
			'Omise: Processing a Passkey payment, redirecting buyer to https://omise.co/passkey/authenticate'
		);
		$order->shouldNotHaveReceived( 'update_meta_data' );
		$order->shouldNotHaveReceived( 'payment_complete' );

		$this->assertEquals(
			[
				'result'   => 'success',
				'redirect' => 'https://omise.co/passkey/authenticate',
			], $result
		);
	}

	public function test_base_card_result_with_failed_charge() {
		$order = $this->get_order_mock( 100, 'thb' );
		Monkey\Functions\expect( 'WC' )->never();

		$charge = array_merge(
			$this->base_charge, [
				'status' => 'failed',
				'authorized' => false,
				'paid' => false,
				'failure_code' => 'brand_not_supported',
				'failure_message' => 'brand not supported',
			]
		);

		$this->expectExceptionMessage( "It seems we've been unable to process your payment properly:<br/>(brand_not_supported) brand not supported" );

		$klass = $this->new_instance();
		$klass->payment_action = 'auto_capture';
		$klass->result( $order->get_id(), $order, $charge );

		$order->shouldHaveReceived( 'add_order_note' )->once()->with(
			'Omise: Payment failed.<br/><b>Error Description:</b> (brand_not_supported) brand not supported'
		);
		$order->shouldHaveReceived( 'update_status' )->once()->with( 'failed' );
		$order->shouldNotHaveReceived( 'update_meta_data' );
		$order->shouldNotHaveReceived( 'payment_complete' );
	}

	public function test_base_card_result_with_unexpected_status_charge() {
		$order = $this->get_order_mock( 100, 'thb' );
		Monkey\Functions\expect( 'WC' )->never();

		$charge = array_merge(
			$this->base_charge, [
				'status' => 'successful',
				'authorized' => false,
				'paid' => false,
			]
		);

		$this->expectExceptionMessage( "It seems we've been unable to process your payment properly:<br/>Note that your payment may have already been processed. Please contact our support team if you have any questions." );

		$klass = $this->new_instance();
		$klass->payment_action = 'auto_capture';
		$klass->result( $order->get_id(), $order, $charge );

		$order->shouldHaveReceived( 'add_order_note' )->once()->with(
			'Omise: Payment failed.<br/><b>Error Description:</b> Note that your payment may have already been processed. Please contact our support team if you have any questions.'
		);
		$order->shouldHaveReceived( 'update_status' )->once()->with( 'failed' );
		$order->shouldNotHaveReceived( 'update_meta_data' );
		$order->shouldNotHaveReceived( 'payment_complete' );
	}
}
