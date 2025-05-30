<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/../gateway/bootstrap-test-setup.php';

class Omise_Event_Charge_Capture_Test extends Bootstrap_Test_Setup
{
	private $base_event_data = [
		'object' => 'charge',
		'id' => 'chrg_test_no1t4tnemucod0e51mo',
		'amount' => 1000,
		'currency' => 'THB',
		'status' => 'paid',
		'paid' => true,
		'metadata' => [
			'order_id' => '100',
		],
	];
	private $wc_order = null;

	protected function setUp(): void
	{
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/exception/OmiseExceptions.php';
		require_once __DIR__ . '/../../../../includes/class-omise-queue-runner.php';
		require_once __DIR__ . '/../../../../includes/class-omise-queueable.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event-charge.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event-charge-capture.php';

		Monkey\Functions\stubs([
			'wp_kses' => null,
		]);

		$this->wc_order = Mockery::mock('WC_Order');
		Monkey\Functions\expect('wc_get_order')
			->with('100')
			->andReturn($this->wc_order);
		$this->wc_order
			->shouldReceive('get_transaction_id')
			->andReturn('chrg_test_no1t4tnemucod0e51mo');
		// $this->wc_order = $order;
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_validate_returns_true_if_charge_paid()
	{
		$charge_event = $this->mock_event_data();
		$this->mockApiCall('omise-charge-get', ['paid' => true]);

		$instance = new Omise_Event_Charge_Capture($charge_event);

		$this->assertTrue($instance->validate());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_validate_returns_false_if_charge_not_paid()
	{
		$charge_event = $this->mock_event_data();
		// Rely on the charge result from the Charge API instead of the event data.
		$this->mockApiCall('omise-charge-get', ['paid' => false]);

		$charge_capture_event = new Omise_Event_Charge_Capture($charge_event);

		$this->assertFalse($charge_capture_event->validate());
	}

	public function test_resolve_successful_charge_order()
	{
		$charge_event = $this->mock_event_data();
		$this->mockApiCall('omise-charge-get', [
			'paid' => true,
			'status' => 'successful',
			'amount' => 12000,
			'currency' => 'THB',
		]);
		$this->wc_order->allows()->get_total()->andReturn('120.00');
		$this->wc_order->allows()->get_currency()->andReturn('THB');

		// Expectation of successful charge order
		$this->wc_order->shouldReceive('add_order_note')
			->once()
			->with('Omise: Received charge.capture webhook event.');
		$this->wc_order->shouldReceive('delete_meta_data')
			->once()
			->with('is_awaiting_capture');
		$this->wc_order->shouldReceive('save')->once();

		$this->wc_order->shouldReceive('add_order_note')
			->once()
			->with('Omise: Payment successful.<br/>An amount 120.00 THB has been paid');
		$this->wc_order
			->shouldReceive('has_status')
			->with('processing')
			->andReturn(true);

		$charge_capture_event = new Omise_Event_Charge_Capture($charge_event);
		$charge_capture_event->validate();
		$charge_capture_event->resolve();
	}

	public function test_resolve_failed_charge_order()
	{
		// TODO: To confirm if failed charge order should be handled.
	}

	private function mock_event_data(array $event_data = [])
	{
		return array_replace_recursive($this->base_event_data, $event_data);
	}
}
