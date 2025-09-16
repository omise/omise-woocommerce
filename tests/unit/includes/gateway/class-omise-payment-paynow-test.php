<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/bootstrap-test-setup.php';

use Brain\Monkey;
use voku\helper\HtmlDomParser;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Paynow_Test extends Bootstrap_Test_Setup {

	private $omise_paynow;
	private $order;
	private $paynow_source = [
		'id' => 'src_test_6459a3zpz5y8lz612fv',
		'type' => 'paynow',
		'scannable_code' => [
			'object' => 'barcode',
			'type' => 'qr',
			'image' => [
				'object' => 'document',
				'id' => 'docu_test_6459a43h7epm4honjq6',
				'kind' => 'qr',
				'download_uri' => 'http://qr.co/documents/docu_test_6459a43h7epm4honjq6/downloads/C47D3436AA6BE90F',
			],
			'raw_data' => null,
		],
	];

	public function setUp(): void {
		parent::setUp();

		// Charge API
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';
		// Paynow
		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offline.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-paynow.php';
		require_once __DIR__ . '/../../../../omise-woocommerce.php';

		Monkey\Functions\stubs(
			[
				'wp_kses' => null,
				'plugins_url' => null,
				'plugin_dir_path' => __DIR__ . '/../../../../',
			]
		);

		$this->mockOmiseSetting( 'pkey_xxx', 'skey_xxx' );
		$this->order = Mockery::mock( 'WC_Order' );
		$this->omise_paynow = Mockery::mock( Omise_Payment_Paynow::class )->makePartial();
		$this->omise_paynow->allows(
			[
				'init_settings' => null,
				'get_option' => 'paynow',
			]
		);
		$this->omise_paynow->__construct();
	}

	public function test_paynow_display_qrcode_returns_qrcode_content() {
		$charge_expires_at = ( new DateTime() )->modify( '+1 hour' )->format( 'c' );

		$this->order->allows(
			[
				'get_id' => 123,
				'get_order_key' => 'wc_order_kSwj6Gcnut4dU',
				'get_transaction_id' => 'chrg_test_1234567890',
			]
		);
		$this->mockApiCall(
			'omise-charge-get',
			[
				'status' => 'pending',
				'expires_at' => $charge_expires_at,
				'source' => $this->paynow_source,
			]
		);

		$order = $this->order;
		Monkey\Functions\stubs(
			[
				'wc_get_order' => function ( $id ) use ( $order ) {
					return $id === 123 ? $order : null;
				},
				'add_query_arg' => function ( $args, $url ) use ( $order ) {
					return $url . '?' . http_build_query( $args );
				},
				'get_rest_url' => function () {
					return 'http://localhost:8080/wp-json/omise/order-status';
				},
			]
		);
		Monkey\Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'omise-paynow-countdown',
				'../assets/javascripts/omise-countdown.js',
				[], WC_VERSION, true
			);
		Monkey\Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'omise-paynow-countdown', 'omise', [
					'countdown_id' => 'timer',
					'qr_expires_at' => $charge_expires_at,
				]
			);
		Monkey\Functions\expect( 'wp_create_nonce' )->twice();
		Monkey\Functions\expect( 'wp_create_nonce' )
			->with( 'get_order_status_wc_order_kSwj6Gcnut4dU' )
			->andReturn( 'abcde12345' );
		Monkey\Functions\expect( 'wp_create_nonce' )
			->with( 'wp_rest' )
			->andReturn( 'fghij67890' );

		ob_start();
		$this->omise_paynow->display_qrcode( $this->order->get_id() );
		$output = ob_get_clean();
		$page = HtmlDomParser::str_get_html( $output );

		$expected_qrcode_img = $this->paynow_source['scannable_code']['image']['download_uri'];
		$this->assertEquals( 'Scan the QR code to pay', $page->findOneOrFalse( '.omise-paynow-details p' )->text() );
		$this->assertEquals( $expected_qrcode_img, $page->findOneOrFalse( '.omise-paynow-qrcode img' )->getAttribute( 'src' ) );
		$this->assertMatchesRegularExpression( '/Payment session will time out in:/', $page->findOneOrFalse( '.omise-paynow-payment-status' )->text() );
		$this->assertNotFalse( $page->findOneOrFalse( '#timer' ) );

		$paynow_detail = $page->findOneOrFalse( '.omise-paynow-details' );
		$this->assertMatchesRegularExpression( '/class="omise omise-paynow-qrcode" style="display:block"/', $paynow_detail );
		$this->assertMatchesRegularExpression( '/class="pending" style="display:block"/', $paynow_detail );
		$this->assertMatchesRegularExpression( '/class="completed" style="display:none"/', $paynow_detail );
		$this->assertMatchesRegularExpression( '/class="timeout" style="display:none"/', $paynow_detail );
	}

	public function test_paynow_display_qrcode_returns_timeout_content_if_qrcode_is_expired() {
		$charge_expires_at = ( new DateTime( 'yesterday' ) )->format( 'c' );

		$this->order->allows(
			[
				'get_id' => 123,
				'get_order_key' => 'wc_order_kSwj6Gcnut4dU',
				'get_transaction_id' => 'chrg_test_1234567890',
			]
		);
		$this->mockApiCall(
			'omise-charge-get',
			[
				'status' => 'pending',
				'expires_at' => $charge_expires_at,
				'source' => $this->paynow_source,
			]
		);

		$order = $this->order;
		Monkey\Functions\stubs(
			[
				'wc_get_order' => function ( $id ) use ( $order ) {
					return $id === 123 ? $order : null;
				},
			]
		);
		Monkey\Functions\expect( 'wp_enqueue_script' )->never();
		Monkey\Functions\expect( 'wp_localize_script' )->never();
		Monkey\Functions\expect( 'wp_create_nonce' )->never();

		ob_start();
		$this->omise_paynow->display_qrcode( $this->order->get_id() );
		$output = ob_get_clean();
		$page = HtmlDomParser::str_get_html( $output );

		$this->assertMatchesRegularExpression( '/const isExpired = \'true\'/', $page->findOneOrFalse( 'script' ) );

		$paynow_detail = $page->findOneOrFalse( '.omise-paynow-details' );
		$this->assertMatchesRegularExpression( '/class="omise omise-paynow-qrcode" style="display:none"/', $paynow_detail );
		$this->assertMatchesRegularExpression( '/class="pending" style="display:none"/', $paynow_detail );
		$this->assertMatchesRegularExpression( '/class="completed" style="display:none"/', $paynow_detail );
		$this->assertMatchesRegularExpression( '/class="timeout" style="display:block"/', $paynow_detail );
	}

	public function test_paynow_display_qrcode_skips_if_order_not_found() {
		Monkey\Functions\stubs( [ 'wc_get_order' => false ] );

		ob_start();
		$this->omise_paynow->display_qrcode( 999 );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	public function test_paynow_display_qrcode_skips_if_charge_status_is_not_pending() {
		$this->order->allows(
			[
				'get_id' => 123,
				'get_transaction_id' => 'chrg_test_1234567890',
			]
		);
		$this->mockApiCall(
			'omise-charge-get',
			[
				'status' => 'successful',
			]
		);

		$order = $this->order;
		Monkey\Functions\stubs(
			[
				'wc_get_order' => function ( $id ) use ( $order ) {
					return $id === 123 ? $order : false;
				},
			]
		);

		ob_start();
		$this->omise_paynow->display_qrcode( $this->order->get_id() );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	public function test_paynow_email_qrcode_returns_qrcode_content() {
		$this->order->allows(
			[
				'get_status' => 'on-hold',
				'get_payment_method' => 'omise_paynow',
				'get_transaction_id' => 'chrg_test_1234567890',
			]
		);
		$this->order->shouldReceive( 'has_status' )
			->with( 'failed' )
			->andReturn( false );
		$this->mockApiCall(
			'omise-charge-get',
			[
				'status' => 'pending',
				'source' => $this->paynow_source,
			]
		);
		Monkey\Functions\expect( 'wp_create_nonce' )->never();

		ob_start();
		$this->omise_paynow->email_qrcode( $this->order, sent_to_admin: false );
		$output = ob_get_clean();
		$page = HtmlDomParser::str_get_html( $output );

		$expected_qrcode_img = $this->paynow_source['scannable_code']['image']['download_uri'];
		$this->assertEquals( 'Scan the QR code to complete', $page->findOne( 'p:first-child' )->text() );
		$this->assertEquals( $expected_qrcode_img, $page->findOne( 'p:last-child img' )->getAttribute( 'src' ) );
	}

	public function test_paynow_email_qrcode_skips_on_admin_email() {
		ob_start();
		$this->omise_paynow->email_qrcode( $this->order, sent_to_admin: true );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	public function test_paynow_email_qrcode_skips_if_order_status_is_processing() {
		$this->order->allows( [ 'get_status' => 'processing' ] );

		ob_start();
		$this->omise_paynow->email_qrcode( $this->order, sent_to_admin: false );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}
}
