<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_UPA_Delegation_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment.php';
		require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offline.php';
		require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-offsite.php';
	}

	public function test_offline_process_payment_delegates_to_upa_checkout_session_flow() {
		$gateway = new class extends Omise_Payment_Offline {
			public function __construct() {}
			protected function process_upa_checkout_session_payment( $order_id ) {
				return array(
					'result' => 'delegated-offline',
					'order_id' => $order_id,
				);
			}
		};

		$result = $gateway->process_payment( 41 );

		$this->assertSame( 'delegated-offline', $result['result'] );
		$this->assertSame( 41, $result['order_id'] );
	}

	public function test_offsite_process_payment_delegates_to_upa_checkout_session_flow() {
		$gateway = new class extends Omise_Payment_Offsite {
			public function __construct() {}
			protected function process_upa_checkout_session_payment( $order_id ) {
				return array(
					'result' => 'delegated-offsite',
					'order_id' => $order_id,
				);
			}
		};

		$result = $gateway->process_payment( 42 );

		$this->assertSame( 'delegated-offsite', $result['result'] );
		$this->assertSame( 42, $result['order_id'] );
	}
}
