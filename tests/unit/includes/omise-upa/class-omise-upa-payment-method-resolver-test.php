<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

class Omise_UPA_Payment_Method_Resolver_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-payment-method-resolver.php';
	}

	protected function tearDown(): void {
		$_POST = array();
		parent::tearDown();
	}

	public function test_resolve_uses_selected_dynamic_source_from_request() {
		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return trim( (string) $value );
				},
				'wp_unslash'          => function( $value ) {
					return $value;
				},
			)
		);

		$_POST['omise-offsite'] = ' mobile_banking_bbl ';

		$gateway = (object) array(
			'id'          => 'omise_mobilebanking',
			'source_type' => '',
		);

		$this->assertSame( 'mobile_banking_bbl', Omise_UPA_Payment_Method_Resolver::resolve( $gateway ) );
	}

	public function test_resolve_falls_back_to_gateway_source_type_for_non_dynamic_gateways() {
		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return trim( (string) $value );
				},
			)
		);

		$gateway = (object) array(
			'id'          => 'omise_truemoney',
			'source_type' => 'truemoney_wallet',
		);

		$this->assertSame( 'truemoney_wallet', Omise_UPA_Payment_Method_Resolver::resolve( $gateway ) );
	}

	public function test_resolve_returns_empty_string_when_source_type_is_not_string() {
		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return trim( (string) $value );
				},
			)
		);

		$gateway = (object) array(
			'id'          => 'omise_promptpay',
			'source_type' => array( 'invalid' ),
		);

		$this->assertSame( '', Omise_UPA_Payment_Method_Resolver::resolve( $gateway ) );
	}
}
