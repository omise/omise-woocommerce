<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Process_Payment_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment.php';

		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return $value;
				},
				'wp_unslash' => function( $value ) {
					return $value;
				},
				'wp_kses' => function( $value ) {
					return $value;
				},
			)
		);
	}

	public function test_process_payment_delegates_to_process_standard_payment() {
		$gateway = new class extends Omise_Payment {
			public function __construct() {}
			public function charge( $order_id, $order ) {}
			public function result( $order_id, $order, $charge ) {}
			protected function process_standard_payment( $order_id ) {
				return array( 'result' => 'delegated', 'order_id' => $order_id );
			}
		};

		$result = $gateway->process_payment( 123 );

		$this->assertSame( 'delegated', $result['result'] );
		$this->assertSame( 123, $result['order_id'] );
	}

	public function test_process_standard_payment_returns_invalid_order_when_load_order_fails() {
		$gateway = new class extends Omise_Payment {
			public $order_to_load = false;
			public $invalid_order_id = null;

			public function __construct() {}
			public function charge( $order_id, $order ) {}
			public function result( $order_id, $order, $charge ) {}
			public function load_order( $order ) {
				return $this->order_to_load;
			}
			protected function invalid_order( $order_id ) {
				$this->invalid_order_id = $order_id;
				return array( 'result' => 'invalid-order' );
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			public function call_process_standard_payment( $order_id ) {
				return $this->process_standard_payment( $order_id );
			}
		};

		$result = $gateway->call_process_standard_payment( 456 );

		$this->assertSame( 'invalid-order', $result['result'] );
		$this->assertSame( 456, $gateway->invalid_order_id );
	}

	public function test_process_standard_payment_creates_charge_and_returns_result() {
		$charge = array( 'id' => 'chrg_test_123' );
		$order  = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'add_order_note' )
			->once()
			->with( Mockery::on( function( $note ) {
				return false !== strpos( $note, 'Omise: Processing a payment with' );
			} ) );
		$order->shouldReceive( 'add_meta_data' )
			->once()
			->with( 'is_omise_payment_resolved', 'no', true );
		$order->shouldReceive( 'save' )->once();
		$order->shouldReceive( 'add_order_note' )->once()->with( 'charge created note' );

		$order_note_helper = Mockery::mock( 'alias:Omise_WC_Order_Note' );
		$order_note_helper->shouldReceive( 'get_charge_created_note' )
			->once()
			->with( $charge )
			->andReturn( 'charge created note' );

		$gateway = new class ( $order, $charge ) extends Omise_Payment {
			public $order_to_load;
			public $charge_to_return;
			public $transaction_id = null;
			public $method_title = 'Test Method';
			public $result_payload = array( 'result' => 'success', 'redirect' => 'https://shop.test/thank-you' );
			public $captured_result_args = array();

			public function __construct( $order, $charge ) {
				$this->order_to_load  = $order;
				$this->charge_to_return = $charge;
			}
			public function load_order( $order ) {
				$this->order = $this->order_to_load;
				return $this->order;
			}
			public function charge( $order_id, $order ) {
				return $this->charge_to_return;
			}
			public function result( $order_id, $order, $charge ) {
				$this->captured_result_args = array(
					'order_id' => $order_id,
					'order' => $order,
					'charge' => $charge,
				);

				return $this->result_payload;
			}
			protected function invalid_order( $order_id ) {
				return array( 'result' => 'invalid-order' );
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			protected function set_order_transaction_id( $transaction_id ) {
				$this->transaction_id = $transaction_id;
			}
			public function call_process_standard_payment( $order_id ) {
				return $this->process_standard_payment( $order_id );
			}
		};

		$result = $gateway->call_process_standard_payment( 99 );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'chrg_test_123', $gateway->transaction_id );
		$this->assertSame( 99, $gateway->captured_result_args['order_id'] );
		$this->assertSame( $charge, $gateway->captured_result_args['charge'] );
	}

	public function test_process_standard_payment_returns_payment_failed_when_charge_throws() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'add_meta_data' )->once();
		$order->shouldReceive( 'save' )->once();

		$gateway = new class ( $order ) extends Omise_Payment {
			public $order_to_load;
			public $method_title = 'Test Method';

			public function __construct( $order ) {
				$this->order_to_load = $order;
			}
			public function load_order( $order ) {
				$this->order = $this->order_to_load;
				return $this->order;
			}
			public function charge( $order_id, $order ) {
				throw new Exception( 'cannot create charge' );
			}
			public function result( $order_id, $order, $charge ) {
				return array( 'result' => 'success' );
			}
			protected function invalid_order( $order_id ) {
				return array( 'result' => 'invalid-order' );
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			public function call_process_standard_payment( $order_id ) {
				return $this->process_standard_payment( $order_id );
			}
		};

		$result = $gateway->call_process_standard_payment( 33 );

		$this->assertSame( 'failed', $result['result'] );
		$this->assertSame( 'cannot create charge', $result['reason'] );
	}

	public function test_process_upa_checkout_session_falls_back_to_standard_when_upa_is_disabled() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->once()->andReturn( false );

		$gateway = new class extends Omise_Payment {
			public function __construct() {}
			public function charge( $order_id, $order ) {}
			public function result( $order_id, $order, $charge ) {}
			protected function process_standard_payment( $order_id ) {
				return array( 'result' => 'standard', 'order_id' => $order_id );
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			public function call_process_upa_checkout_session_payment( $order_id ) {
				return $this->process_upa_checkout_session_payment( $order_id );
			}
		};

		$result = $gateway->call_process_upa_checkout_session_payment( 19 );

		$this->assertSame( 'standard', $result['result'] );
		$this->assertSame( 19, $result['order_id'] );
	}

	public function test_process_upa_checkout_session_returns_invalid_order_when_load_fails() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$gateway = new class extends Omise_Payment {
			public function __construct() {}
			public function charge( $order_id, $order ) {}
			public function result( $order_id, $order, $charge ) {}
			public function load_order( $order ) {
				return false;
			}
			protected function invalid_order( $order_id ) {
				return array( 'result' => 'invalid-order', 'order_id' => $order_id );
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			public function call_process_upa_checkout_session_payment( $order_id ) {
				return $this->process_upa_checkout_session_payment( $order_id );
			}
		};

		$result = $gateway->call_process_upa_checkout_session_payment( 101 );

		$this->assertSame( 'invalid-order', $result['result'] );
		$this->assertSame( 101, $result['order_id'] );
	}

	public function test_process_upa_checkout_session_falls_back_to_standard_when_feature_flag_is_disabled_for_order() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$feature_flag = Mockery::mock( 'alias:Omise_UPA_Feature_Flag' );
		$feature_flag->shouldReceive( 'is_enabled_for_order' )->once()->andReturn( false );

		$order = Mockery::mock( 'WC_Order' );

		$gateway = new class ( $order ) extends Omise_Payment {
			public $order_to_load;
			public $load_order_calls = 0;
			public function __construct( $order ) {
				$this->order_to_load = $order;
			}
			public function charge( $order_id, $order ) {}
			public function result( $order_id, $order, $charge ) {}
			public function load_order( $order ) {
				$this->load_order_calls++;
				$this->order = $this->order_to_load;
				return $this->order;
			}
			protected function process_standard_payment_with_loaded_order( $order_id ) {
				return array( 'result' => 'standard', 'order_id' => $order_id );
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			public function call_process_upa_checkout_session_payment( $order_id ) {
				return $this->process_upa_checkout_session_payment( $order_id );
			}
		};

		$result = $gateway->call_process_upa_checkout_session_payment( 11 );

		$this->assertSame( 'standard', $result['result'] );
		$this->assertSame( 11, $result['order_id'] );
		$this->assertSame( 1, $gateway->load_order_calls );
	}

	public function test_process_upa_checkout_session_calls_session_service_when_enabled() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$feature_flag = Mockery::mock( 'alias:Omise_UPA_Feature_Flag' );
		$feature_flag->shouldReceive( 'is_enabled_for_order' )->once()->andReturn( true );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'add_meta_data' )
			->once()
			->with( 'is_omise_payment_resolved', 'no', true );
		$order->shouldReceive( 'save' )->once();

		$gateway = new class ( $order ) extends Omise_Payment {
			public $order_to_load;
			public $method_title = 'UPA Method';

			public function __construct( $order ) {
				$this->order_to_load = $order;
			}
			public function charge( $order_id, $order ) {}
			public function result( $order_id, $order, $charge ) {}
			public function load_order( $order ) {
				$this->order = $this->order_to_load;
				return $this->order;
			}
			protected function process_standard_payment( $order_id ) {
				return array( 'result' => 'standard', 'order_id' => $order_id );
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			public function call_process_upa_checkout_session_payment( $order_id ) {
				return $this->process_upa_checkout_session_payment( $order_id );
			}
		};

		$session_service = Mockery::mock( 'alias:Omise_UPA_Session_Service' );
		$session_service->shouldReceive( 'create_checkout_session' )
			->once()
			->with( $gateway, 202, $order )
			->andReturn( array( 'result' => 'success', 'redirect' => 'https://upa.example.com/pay' ) );

		$result = $gateway->call_process_upa_checkout_session_payment( 202 );

		$this->assertSame( 'success', $result['result'] );
		$this->assertSame( 'https://upa.example.com/pay', $result['redirect'] );
	}

	public function test_process_upa_checkout_session_returns_payment_failed_when_session_creation_throws() {
		$setting = Mockery::mock( 'alias:Omise_Setting' );
		$setting->shouldReceive( 'instance' )->andReturn( $setting );
		$setting->shouldReceive( 'is_upa_enabled' )->andReturn( true );

		$feature_flag = Mockery::mock( 'alias:Omise_UPA_Feature_Flag' );
		$feature_flag->shouldReceive( 'is_enabled_for_order' )->once()->andReturn( true );

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'add_order_note' )->once();
		$order->shouldReceive( 'add_meta_data' )->once();
		$order->shouldReceive( 'save' )->once();

		$gateway = new class ( $order ) extends Omise_Payment {
			public $order_to_load;
			public $method_title = 'UPA Method';

			public function __construct( $order ) {
				$this->order_to_load = $order;
			}
			public function charge( $order_id, $order ) {}
			public function result( $order_id, $order, $charge ) {}
			public function load_order( $order ) {
				$this->order = $this->order_to_load;
				return $this->order;
			}
			protected function payment_failed( $charge, $reason = '' ) {
				return array( 'result' => 'failed', 'reason' => $reason );
			}
			public function call_process_upa_checkout_session_payment( $order_id ) {
				return $this->process_upa_checkout_session_payment( $order_id );
			}
		};

		$session_service = Mockery::mock( 'alias:Omise_UPA_Session_Service' );
		$session_service->shouldReceive( 'create_checkout_session' )
			->once()
			->andThrow( new Exception( 'session is unavailable' ) );

		$result = $gateway->call_process_upa_checkout_session_payment( 888 );

		$this->assertSame( 'failed', $result['result'] );
		$this->assertSame( 'session is unavailable', $result['reason'] );
	}
}
