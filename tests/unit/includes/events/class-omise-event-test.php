<?php
require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/../gateway/bootstrap-test-setup.php';

class Omise_Event_Test extends Bootstrap_Test_Setup {
	private $instance = null;

	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/class-omise-queue-runner.php';
		require_once __DIR__ . '/../../../../includes/class-omise-queueable.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event.php';

		$this->instance = new class('event') extends Omise_Event {};
	}

	public function test_event_validate_returns_true() {
		$this->assertTrue( $this->instance->validate() );
	}

	public function test_event_resolve_returns_true() {
		$this->assertTrue( $this->instance->resolve() );
	}

	public function test_event_get_data_returns_instance_data() {
		$this->assertEquals( 'event', $this->instance->get_data() );
	}

	public function test_event_get_order_returns_instance_order() {
		$this->assertNull( $this->instance->get_order() );
	}
}
