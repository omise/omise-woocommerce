<?php

use Brain\Monkey;
use voku\helper\HtmlDomParser;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_Atome_Test extends Omise_Payment_Offsite_Test {

	private $omise_atome;

	protected function setUp(): void {
		parent::setUp();

		$this->omise_atome = $this->mock_payment_class( Omise_Payment_Atome::class );
	}

	public function test_atome_get_charge_request() {
		$order_amount = 4566;
		$order_currency = 'THB';
		$order_id = 'order_123';
		$order_mock = $this->getOrderMock( $order_amount, $order_currency );

		$wc_product = Mockery::mock( 'overload:WC_Product' );
		$wc_product->shouldReceive( 'get_sku' )
			->once()
			->andReturn( 'sku_1234' );

		$_POST['omise_atome_phone_default'] = true;

		$result = $this->omise_atome->get_charge_request( $order_id, $order_mock );

		$this->assertEquals( 456600, $result['amount'] );
		$this->assertEquals( $order_currency, $result['currency'] );
		$this->assertEquals( $order_id, $result['metadata']['order_id'] );
		$this->assertEquals( $this->return_uri, $result['return_uri'] );

		$expected_source = [
			'type' => 'atome',
			'phone_number' => $order_mock->get_billing_phone(),
			'items' => [
				[
					'name' => 'T Shirt',
					'amount' => 60000,
					'quantity' => 1,
					'sku' => 'sku_1234',
				],
			],
			'shipping' => [
				'country' => 'Thailand',
				'city' => 'Bangkok',
				'postal_code' => '10110',
				'state' => 'Bangkok',
				'street1' => 'Sukumvit Road',
			],
		];
		$this->assertEquals( $expected_source, $result['source'] );
	}

	public function test_atome_get_charge_request_with_custom_phone_number() {
		$order_amount = 4566;
		$order_currency = 'THB';
		$order_id = 'order_123';
		$order_mock = $this->getOrderMock( $order_amount, $order_currency );

		$wc_product = Mockery::mock( 'overload:WC_Product' );
		$wc_product->shouldReceive( 'get_sku' )
			->once()
			->andReturn( 'sku_1234' );

		$_POST['omise_atome_phone_default'] = false;
		$_POST['omise_atome_phone_number'] = '+66123456789';

		$result = $this->omise_atome->get_charge_request( $order_id, $order_mock );

		$this->assertEquals( 456600, $result['amount'] );
		$this->assertEquals( $order_currency, $result['currency'] );
		$this->assertEquals( 'atome', $result['source']['type'] );
		$this->assertEquals( '+66123456789', $result['source']['phone_number'] );
	}

	public function test_atome_charge() {
		$order = $this->getOrderMock( 999999, 'THB' );
		$_POST['omise_atome_phone_default'] = true;

		$this->perform_charge_test( $this->omise_atome, $order );
	}

	public function test_atome_payment_fields_renders_atome_form_on_checkout_page() {
		$cart = $this->get_cart_mock(
			[
				'subtotal' => 300,
				'total' => 380,
			]
		);
		$wc = $this->get_wc_mock( $cart );

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
		Monkey\Functions\expect( 'get_woocommerce_currency' )->andReturn( 'THB' );
		Monkey\Functions\expect( 'is_checkout_pay_page' )->andReturn( false );

		ob_start();
		$this->omise_atome->payment_fields();
		$output = ob_get_clean();

		$page = HtmlDomParser::str_get_html( $output );
		$this->assertMatchesRegularExpression( '/Atome phone number/', $page->findOne( '#omise-form-atome' )->innertext );
	}

	public function test_atome_payment_fields_renders_atome_form_on_pay_for_order_page() {
		$wc = $this->get_wc_mock();
		$order_mock = $this->getOrderMock( 380, 'THB' );
		$order_mock->shouldReceive( 'get_subtotal' )->andReturn( 300 );

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
		Monkey\Functions\expect( 'get_woocommerce_currency' )->andReturn( 'THB' );
		Monkey\Functions\expect( 'is_checkout_pay_page' )->andReturn( true );
		Monkey\Functions\expect( 'get_query_var' )->with( 'order-pay' )->andReturn( 456 );
		Monkey\Functions\expect( 'wc_get_order' )->with( 456 )->andReturn( $order_mock );

		ob_start();
		$this->omise_atome->payment_fields();
		$output = ob_get_clean();

		$page = HtmlDomParser::str_get_html( $output );
		$this->assertMatchesRegularExpression( '/Atome phone number/', $page->findOne( '#omise-form-atome' )->innertext );
	}

	public function test_atome_payment_fields_returns_error_if_subtotal_is_zero() {
		$cart = $this->get_cart_mock(
			[
				'subtotal' => 0,
				'total' => 100,
			]
		);
		$wc = $this->get_wc_mock( $cart );

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
		Monkey\Functions\expect( 'get_woocommerce_currency' )->andReturn( 'THB' );
		Monkey\Functions\expect( 'is_checkout_pay_page' )->andReturn( false );

		ob_start();
		$this->omise_atome->payment_fields();
		$output = ob_get_clean();

		$this->assertEquals( 'Complimentary products cannot be billed.', trim( $output ) );
	}

	public function test_atome_payment_fields_returns_error_if_currency_not_support() {
		$cart = $this->get_cart_mock(
			[
				'subtotal' => 100,
				'total' => 100,
			]
		);
		$wc = $this->get_wc_mock( $cart );

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
		Monkey\Functions\expect( 'get_woocommerce_currency' )->andReturn( 'USD' );
		Monkey\Functions\expect( 'is_checkout_pay_page' )->andReturn( false );

		ob_start();
		$this->omise_atome->payment_fields();
		$output = ob_get_clean();

		$this->assertEquals( 'Currency not supported', trim( $output ) );
	}

	public function test_atome_payment_fields_returns_error_if_amount_less_than_min_limit() {
		$cart = $this->get_cart_mock(
			[
				'subtotal' => 1.4,
				'total' => 1.4,
			]
		);
		$wc = $this->get_wc_mock( $cart );

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
		Monkey\Functions\expect( 'get_woocommerce_currency' )->andReturn( 'SGD' );
		Monkey\Functions\expect( 'is_checkout_pay_page' )->andReturn( false );

		ob_start();
		$this->omise_atome->payment_fields();
		$output = ob_get_clean();

		$this->assertEquals( 'Amount must be greater than 1.50 SGD', trim( $output ) );
	}

	public function test_atome_payment_fields_returns_error_if_amount_greater_than_max_limit() {
		$cart = $this->get_cart_mock(
			[
				'subtotal' => 20001,
				'total' => 20001,
			]
		);
		$wc = $this->get_wc_mock( $cart );

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
		Monkey\Functions\expect( 'get_woocommerce_currency' )->andReturn( 'SGD' );
		Monkey\Functions\expect( 'is_checkout_pay_page' )->andReturn( false );

		ob_start();
		$this->omise_atome->payment_fields();
		$output = ob_get_clean();

		$this->assertEquals( 'Amount must be less than 20,000.00 SGD', trim( $output ) );
	}

	public function test_atome_payment_fields_throws_exception_if_pay_for_order_not_found() {
		$wc = $this->get_wc_mock();

		Monkey\Functions\expect( 'WC' )->andReturn( $wc );
		Monkey\Functions\expect( 'get_woocommerce_currency' )->andReturn( 'THB' );
		Monkey\Functions\expect( 'is_checkout_pay_page' )->andReturn( true );
		Monkey\Functions\expect( 'get_query_var' )->with( 'order-pay' )->andReturn( 456 );
		Monkey\Functions\expect( 'wc_get_order' )->with( 456 )->andReturn( false );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Order not found.' );

		$this->omise_atome->payment_fields();
	}

	public function test_atome_get_icon() {
		Monkey\Functions\expect( 'apply_filters' )
		->once()
		->with(
			'woocommerce_gateway_icon',
			"<img src='../assets/images/atome.svg' class='Omise-Image' style='width: 30px; max-height: 30px;' alt='Atome logo' />",
			'omise_atome'
		);

		$this->omise_atome->get_icon();
	}
}
