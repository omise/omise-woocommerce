<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/../gateway/bootstrap-test-setup.php';

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Event_Charge_Complete_Test extends Bootstrap_Test_Setup {

	private $base_event_data = [
		'object' => 'charge',
		'id' => 'chrg_test_no1t4tnemucod0e51mo',
		'amount' => 1000,
		'currency' => 'THB',
		'status' => 'successful',
		'paid' => true,
		'metadata' => [
			'order_id' => '100',
		],
	];
	private $wc_order = null;
	private $omise_queue = null;

	protected function setUp(): void {
		parent::setUp();
		require_once __DIR__ . '/../../../../includes/class-omise-queueable.php';
		$this->omise_queue = Mockery::mock( Omise_Queueable::class )->makePartial();

		require_once __DIR__ . '/../../../../omise-woocommerce.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/exception/OmiseExceptions.php';
		require_once __DIR__ . '/../../../../includes/class-omise-localization.php';
		require_once __DIR__ . '/../../../../includes/class-omise-queue-runner.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event-charge.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event-charge-complete.php';
		require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
		require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment.php';

		Monkey\Functions\stubs(
			[
				'wp_kses' => null,
			]
		);

		$this->wc_order = Mockery::mock( 'WC_Order' );
		Monkey\Functions\expect( 'wc_get_order' )
			->with( '100' )
			->andReturn( $this->wc_order );
		$this->wc_order
			->shouldReceive( 'get_transaction_id' )
			->andReturn( 'chrg_test_no1t4tnemucod0e51mo' );
	}

	public function test_resolve_schedules_action_to_process_later_if_callback_has_not_processed() {
		$this->mockApiCall( 'omise-charge-get' );
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'no' );

		$charge_event = $this->mock_event_data();
		$mock = Mockery::mock( Omise_Event_Charge_Complete::class, [ $charge_event ] )->makePartial();

		$mock->shouldReceive( 'schedule_single' )->once()->andReturn( true );

		$mock->validate();
		$mock->resolve();
	}

	public function test_resolve_successful_charge_order() {
		$this->mockApiCall( 'omise-charge-get', [ 'status' => 'successful' ] );
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );
		$this->wc_order->shouldReceive( 'has_status' )
			->with( 'processing' )
			->andReturn( false );
		$this->wc_order->allows()->get_total()->andReturn( '180.00' );
		$this->wc_order->allows()->get_currency()->andReturn( 'THB' );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );
		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Payment successful.<br/>An amount 180.00 THB has been paid' );
		$this->wc_order->shouldReceive( 'payment_complete' )->once();

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	public function test_resolve_successful_charge_order_skips_if_order_is_already_processing() {
		$this->mockApiCall( 'omise-charge-get', [ 'status' => 'successful' ] );
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );
		$this->wc_order->shouldReceive( 'has_status' )
			->with( 'processing' )
			->andReturn( true );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	public function test_resolve_failed_charge_order() {
		$this->mockApiCall(
			'omise-charge-get', [
				'status' => 'failed',
				'paid' => false,
				'failure_message' => 'capture failed',
				'failure_code' => 'failed_capture',
			]
		);
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );
		$this->wc_order->shouldReceive( 'has_status' )
			->with( 'failed' )
			->andReturn( false );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );
		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Payment failed.<br/>capture failed (code: failed_capture)' );
		$this->wc_order->shouldReceive( 'update_status' )->once()->with( 'failed' );

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	public function test_resolve_failed_charge_order_skips_if_order_is_already_failed() {
		$this->mockApiCall( 'omise-charge-get', [ 'status' => 'failed' ] );
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );
		$this->wc_order->shouldReceive( 'has_status' )
			->with( 'failed' )
			->andReturn( true );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	public function test_resolve_pending_charge_order() {
		$this->mockApiCall(
			'omise-charge-get', [
				'status' => 'pending',
				'authorized' => false,
			]
		);
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );
		$this->wc_order->shouldReceive( 'has_status' )
			->with( 'processing' )
			->andReturn( false );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );
		$this->wc_order->shouldReceive( 'add_order_note' )->never();
		$this->wc_order->shouldReceive( 'update_status' )->never();

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	public function test_resolve_pending_authorized_charge_order() {
		$this->mockApiCall(
			'omise-charge-get', [
				'status' => 'pending',
				'authorized' => true,
			]
		);
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );
		$this->wc_order->shouldReceive( 'has_status' )
			->with( 'processing' )
			->andReturn( false );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );
		$this->wc_order->shouldReceive( 'update_meta_data' )
			->once()
			->with( 'is_awaiting_capture', 'yes' );
		$this->wc_order->shouldReceive( 'update_status' )
			->once()
			->with( 'processing' );
		$this->wc_order->shouldReceive( 'save' )->once();

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	public function test_resolve_pending_charge_order_skips_if_order_is_already_processing() {
		$this->mockApiCall( 'omise-charge-get', [ 'status' => 'pending' ] );
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );
		$this->wc_order->shouldReceive( 'has_status' )
			->with( 'processing' )
			->andReturn( true );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );
		$this->wc_order->shouldReceive( 'add_order_note' )->never();
		$this->wc_order->shouldReceive( 'update_status' )->never();

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	/**
	 * @dataProvider unexpected_statuses_provider
	 */
	public function test_resolve_does_nothing_if_status_is_in_unexpected_statuses( $status ) {
		$this->mockApiCall( 'omise-charge-get', [ 'status' => $status ] );
		$this->wc_order->shouldReceive( 'get_meta' )
			->with( 'is_omise_payment_resolved' )
			->andReturn( 'yes' );

		$charge_event = $this->mock_event_data();
		$complete_charge_event = new Omise_Event_Charge_Complete( $charge_event );

		$this->wc_order->shouldReceive( 'add_order_note' )
			->once()
			->with( 'Omise: Received charge.complete webhook event.' );
		$this->wc_order->shouldReceive( 'add_order_note' )->never();
		$this->wc_order->shouldReceive( 'update_status' )->never();
		$this->wc_order->shouldReceive( 'payment_complete' )->never();

		$complete_charge_event->validate();
		$complete_charge_event->resolve();
	}

	public function unexpected_statuses_provider(): array {
		return [
			'expired status' => [ 'expired' ],
			'reversed status' => [ 'reversed' ],
		];
	}

	private function mock_event_data( array $event_data = [] ) {
		return array_replace_recursive( $this->base_event_data, $event_data );
	}
}
