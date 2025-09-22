<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Base_Card_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		Monkey\Functions\stubs(
			[
				'add_action',
				'add_filter',
				'do_action',
				'wc_clean' => null,
				'wc_string_to_bool' => function ( $val ) {
					return $val === 'yes' || $val === '1';
				},
			]
		);

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

		$klass = $this->new_instance( [ 'is_passkey_enabled' => 'no' ] );
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

	public function test_base_card_charge_with_wc_block_and_passkey_enabled() {
		$charge_mock = Mockery::mock( 'overload:OmiseCharge' );
		$charge_mock->shouldReceive( 'create' )->once()->andReturn( [ 'object' => 'charge' ] );
		$order_mock = $this->get_order_mock( 2000, 'thb' );

		$_POST['omise_token'] = 'tokn_567';
		$_POST['omise_save_customer_card'] = '';
		$_POST['wc_block_payment'] = '1';

		$klass = $this->new_instance( [ 'is_passkey_enabled' => 'yes' ] );
		$klass->payment_action = 'auto_capture';
		$klass->charge( $order_mock->get_id(), $order_mock );

		$charge_mock->shouldHaveReceived( 'create' )->once()->with(
			[
				'amount' => 200000,
				'currency' => 'THB',
				'description' => 'WooCommerce Order id 123',
				'return_uri' => 'https://abc.com/order/complete',
				'metadata' => [ 'order_id' => 123 ],
				'card' => 'tokn_567',
				'capture' => true,
				'authentication' => 'PASSKEY',
			]
		);
	}

	public function test_base_card_charge_with_wc_shortcode_and_passkey_enabled() {
		$charge_mock = Mockery::mock( 'overload:OmiseCharge' );
		$charge_mock->shouldReceive( 'create' )->once()->andReturn( [ 'object' => 'charge' ] );
		$order_mock = $this->get_order_mock( 100.50, 'thb' );

		$_POST['omise_token'] = 'tokn_567';
		$_POST['omise_save_customer_card'] = '';

		$klass = $this->new_instance( [ 'is_passkey_enabled' => 'yes' ] );
		$klass->payment_action = 'auto_capture';
		$klass->charge( $order_mock->get_id(), $order_mock );

		$charge_mock->shouldHaveReceived( 'create' )->once()->with(
			[
				'amount' => 10050,
				'currency' => 'THB',
				'description' => 'WooCommerce Order id 123',
				'return_uri' => 'https://abc.com/order/complete',
				'metadata' => [ 'order_id' => 123 ],
				'card' => 'tokn_567',
				'capture' => true,
			]
		);
	}
}
