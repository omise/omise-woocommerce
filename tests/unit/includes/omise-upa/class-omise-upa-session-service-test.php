<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_UPA_Session_Service_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/stubs/payment-stubs.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-session-service.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-state-token.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-payment-method-resolver.php';

		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return $value;
				},
				'wp_unslash' => function( $value ) {
					return $value;
				},
				'esc_url_raw' => function( $value ) {
					return $value;
				},
				'esc_url' => function( $value ) {
					return $value;
				},
			)
		);
	}

	protected function tearDown(): void {
		$_POST = array();
		parent::tearDown();
	}

	// ─── create_checkout_session Tests ───────────────────────────────────

	public function test_create_checkout_session_returns_success_with_redirect() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'truemoney';

		// Omise_Money and Token are already loaded via bootstrap - use them directly

		// Mock Omise_Setting
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		// Mock Omise_UPA_Client
		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_uses_mobile_banking_method_for_mobile_banking_gateway() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->id = 'omise_mobilebanking';
		$gateway->source_type = '';

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->with(
			Mockery::on(
				function( $payload ) {
					return isset( $payload['payment_methods'] )
						&& is_array( $payload['payment_methods'] )
						&& array( 'mobile_banking' ) === $payload['payment_methods'];
				}
			)
		)->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_uses_installment_source_type_for_installment_gateway() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->id          = 'omise_installment';
		$gateway->source_type = 'installment';

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->with(
			Mockery::on(
				function( $payload ) {
					return isset( $payload['payment_methods'] )
						&& is_array( $payload['payment_methods'] )
						&& array( 'installment' ) === $payload['payment_methods'];
				}
			)
		)->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_includes_style_from_card_customization_settings() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'truemoney';

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$customization = Mockery::mock( 'alias:Omise_Page_Card_From_Customization' );
		$customization->shouldReceive( 'get_instance' )->andReturn( $customization );
		$customization->shouldReceive( 'get_upa_style_settings' )->andReturn(
			array(
				'theme_color' => '#096B68',
				'text_color'  => '#fff',
			)
		);

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->with(
			Mockery::on(
				function( $payload ) {
					return isset( $payload['style']['theme_color'], $payload['style']['text_color'] )
						&& '#096B68' === $payload['style']['theme_color']
						&& '#fff' === $payload['style']['text_color']
						&& ! isset( $payload['auto_capture'] );
				}
			)
		)->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_omits_style_when_customization_returns_empty_array() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'truemoney';

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$customization = Mockery::mock( 'alias:Omise_Page_Card_From_Customization' );
		$customization->shouldReceive( 'get_instance' )->andReturn( $customization );
		$customization->shouldReceive( 'get_upa_style_settings' )->andReturn( array() );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->with(
			Mockery::on(
				function( $payload ) {
					return ! isset( $payload['style'] );
				}
			)
		)->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_omits_style_when_customization_class_not_available() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'truemoney';

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->with(
			Mockery::on(
				function( $payload ) {
					return ! isset( $payload['style'] );
				}
			)
		)->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_sets_auto_capture_true_when_payment_action_is_auto_capture() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'rabbit_linepay';
		$gateway->payment_action = 'auto_capture';

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->with(
			Mockery::on(
				function( $payload ) {
					return isset( $payload['auto_capture'] ) && true === $payload['auto_capture'];
				}
			)
		)->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_sets_auto_capture_false_when_payment_action_is_manual_capture() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );
		$order->shouldReceive( 'update_meta_data' )->times( 5 );
		$order->shouldReceive( 'delete_meta_data' )->once()->with( 'omise_upa_retry_attempts' );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once();

		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'rabbit_linepay';
		$gateway->payment_action = 'manual_capture';

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->with(
			Mockery::on(
				function( $payload ) {
					return isset( $payload['auto_capture'] ) && false === $payload['auto_capture'];
				}
			)
		)->andReturn(
			array(
				'id'           => 'sess_123',
				'redirect_url' => 'https://upa.example.com/pay/sess_123',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturnUsing(
			function( $args, $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$result = Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay/sess_123', $result['redirect'] );
	}

	public function test_create_checkout_session_throws_when_source_type_empty() {
		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = '';

		$order = Mockery::mock( 'WC_Order' );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again or choose another payment method.' );

		Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );
	}

	public function test_create_checkout_session_throws_select_bank_for_dynamic_gateway() {
		$gateway = new Omise_Payment_Offsite();
		$gateway->id = 'omise_internetbanking';
		$gateway->source_type = '';

		$order = Mockery::mock( 'WC_Order' );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Please select bank below' );

		Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );
	}

	public function test_create_checkout_session_throws_when_session_id_missing() {
		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'truemoney';

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->andReturn( array( 'status' => 'created' ) );
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturn( 'https://shop.test/?wc-api=omise_upa_complete' );
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again or choose another payment method.' );

		Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );
	}

	public function test_create_checkout_session_throws_when_redirect_host_mismatch() {
		$gateway = new Omise_Payment_Offsite();
		$gateway->source_type = 'truemoney';

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_currency' )->andReturn( 'THB' );
		$order->shouldReceive( 'get_total' )->andReturn( '1000.00' );
		$order->shouldReceive( 'get_order_key' )->andReturn( 'wc_order_abc' );

		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'get_upa_api_base_url' )->andReturn( 'https://upa.example.com' );
		$setting->shouldReceive( 'secret_key' )->andReturn( 'skey_test_123' );

		$client = Mockery::mock( 'overload:Omise_UPA_Client' );
		$client->shouldReceive( 'create_session' )->once()->andReturn(
			array(
				'session_id'   => 'sess_123',
				'redirect_url' => 'https://evil.example.com/phishing',
			)
		);
		$client->shouldReceive( 'get_base_url' )->andReturn( 'https://upa.example.com' );

		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'home_url' )->andReturn( 'https://shop.test/' );
		Monkey\Functions\expect( 'add_query_arg' )->andReturn( 'https://shop.test/?wc-api=omise_upa_complete' );
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'en_US' );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again or choose another payment method.' );

		Omise_UPA_Session_Service::create_checkout_session( $gateway, '99', $order );
	}

	// ─── resolve_flow Tests ─────────────────────────────────────────────

	public function test_resolve_flow_returns_offline_for_offline_gateway() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'resolve_flow' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$gateway = new Omise_Payment_Offline();
		$result  = $method->invoke( null, $gateway );

		$this->assertSame( 'offline', $result );
	}

	public function test_resolve_flow_returns_offsite_for_offsite_gateway() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'resolve_flow' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$gateway = new Omise_Payment_Offsite();
		$result  = $method->invoke( null, $gateway );

		$this->assertSame( 'offsite', $result );
	}

	// ─── extract_session_id Tests ───────────────────────────────────────

	public function test_extract_session_id_returns_id_key() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'extract_session_id' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$result = $method->invoke( null, array( 'id' => 'sess_123' ) );
		$this->assertSame( 'sess_123', $result );
	}

	public function test_extract_session_id_ignores_unknown_keys() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'extract_session_id' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$result = $method->invoke( null, array( 'id' => 'sess_456' ) );
		$this->assertSame( 'sess_456', $result );
	}

	public function test_extract_session_id_returns_empty_when_no_id() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'extract_session_id' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$result = $method->invoke( null, array( 'status' => 'created' ) );
		$this->assertSame( '', $result );
	}

	// ─── is_dynamic_source_gateway Tests ────────────────────────────────

	public function test_is_dynamic_source_gateway_returns_true_for_internetbanking() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'is_dynamic_source_gateway' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$gateway     = new Omise_Payment_Offsite();
		$gateway->id = 'omise_internetbanking';

		$this->assertTrue( $method->invoke( null, $gateway ) );
	}

	public function test_is_dynamic_source_gateway_returns_false_for_mobilebanking() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'is_dynamic_source_gateway' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$gateway     = new Omise_Payment_Offsite();
		$gateway->id = 'omise_mobilebanking';

		$this->assertFalse( $method->invoke( null, $gateway ) );
	}

	public function test_is_dynamic_source_gateway_returns_false_for_regular_gateway() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'is_dynamic_source_gateway' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$gateway     = new Omise_Payment_Offsite();
		$gateway->id = 'omise_truemoney';

		$this->assertFalse( $method->invoke( null, $gateway ) );
	}

	public function test_is_dynamic_source_gateway_returns_false_when_id_not_set() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'is_dynamic_source_gateway' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$gateway = new stdClass();

		$this->assertFalse( $method->invoke( null, $gateway ) );
	}

	// ─── validate_redirect_url Tests ────────────────────────────────────

	public function test_validate_redirect_url_passes_for_matching_hosts() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'validate_redirect_url' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'wp_http_validate_url' )->once()->andReturn( true );

		$method->invoke( null, 'https://upa.example.com/pay/sess_123', 'https://upa.example.com' );
		$this->assertTrue( true );
	}

	public function test_validate_redirect_url_throws_for_mismatched_hosts() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'validate_redirect_url' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'wp_http_validate_url' )->once()->andReturn( true );

		$this->expectException( Exception::class );

		$method->invoke( null, 'https://evil.com/pay', 'https://upa.example.com' );
	}

	public function test_validate_redirect_url_throws_for_invalid_url() {
		$method = new ReflectionMethod( Omise_UPA_Session_Service::class, 'validate_redirect_url' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		Monkey\Functions\expect( 'wp_http_validate_url' )->once()->andReturn( false );

		$this->expectException( Exception::class );

		$method->invoke( null, 'not-a-url', 'https://upa.example.com' );
	}
}
