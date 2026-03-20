<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/bootstrap-test-setup.php';

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Promptpay_Email_Guard_Test extends Bootstrap_Test_Setup {
	private $gateway;

	public function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offline.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-session-service.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-promptpay.php';
		require_once __DIR__ . '/../../../../omise-woocommerce.php';

		Monkey\Functions\stubs(
			[
				'wp_kses' => null,
				'plugins_url' => null,
				'plugin_dir_path' => __DIR__ . '/../../../../',
				'wp_enqueue_script' => null,
			]
		);

		$this->mockOmiseSetting( 'pkey_xxx', 'skey_xxx' );
		$this->gateway = Mockery::mock( Omise_Payment_Promptpay::class )->makePartial();
		$this->gateway->allows(
			[
				'init_settings' => null,
				'get_option' => 'promptpay',
			]
		);
		$this->gateway->__construct();
	}

	public function test_email_qrcode_skips_on_admin_email() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldNotReceive( 'get_status' );
		$order->shouldNotReceive( 'get_payment_method' );
		$this->gateway->shouldNotReceive( 'display_qrcode' );

		$result = $this->gateway->email_qrcode( $order, true );

		$this->assertNull( $result );
	}

	public function test_email_qrcode_skips_when_order_is_processing() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_status' )->once()->andReturn( 'processing' );
		$order->shouldNotReceive( 'get_payment_method' );
		$this->gateway->shouldNotReceive( 'display_qrcode' );

		$result = $this->gateway->email_qrcode( $order, false );

		$this->assertNull( $result );
	}

	public function test_email_qrcode_calls_display_qrcode_for_non_processing_promptpay_order() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_status' )->once()->andReturn( 'on-hold' );
		$order->shouldReceive( 'get_payment_method' )->once()->andReturn( 'omise_promptpay' );
		$this->gateway->shouldReceive( 'display_qrcode' )->once()->with( $order, 'email' );

		$result = $this->gateway->email_qrcode( $order, false );

		$this->assertNull( $result );
	}
}

