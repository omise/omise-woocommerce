<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/../gateway/bootstrap-test-setup.php';

class Omise_Event_Charge_Test extends Bootstrap_Test_Setup {
	private $base_event_data = [
		'object' => 'charge',
		'id' => 'chrg_test_no1t4tnemucod0e51mo',
		'amount' => 1000,
		'currency' => 'THB',
		'status' => 'successful',
		'metadata' => [
			'order_id' => '100',
		],
	];

	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';
		require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/exception/OmiseExceptions.php';
		require_once __DIR__ . '/../../../../includes/class-omise-queue-runner.php';
		require_once __DIR__ . '/../../../../includes/class-omise-queueable.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event.php';
		require_once __DIR__ . '/../../../../includes/events/class-omise-event-charge.php';
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_validate_returns_true_for_valid_order_payment() {
		$charge_event = $this->mock_event_data();
		$order = Mockery::mock( 'WC_Order' );

		Monkey\Functions\expect( 'wc_get_order' )
			->with( '100' )
			->andReturn( $order );
		$order->shouldReceive( 'get_transaction_id' )
			->andReturn( 'chrg_test_no1t4tnemucod0e51mo' );
		$this->mockApiCall( 'omise-charge-get' );

		$instance = new class($charge_event) extends Omise_Event_Charge {
			public function get_charge() {
				return $this->charge;
			}
		};

		$this->assertTrue( $instance->validate() );
		$this->assertInstanceOf( OmiseCharge::class, $instance->get_charge() );
	}

	public function test_validate_returns_false_for_non_charge_event() {
		$customer_event = $this->mock_event_data( [ 'object' => 'customer' ] );

		$instance = new class($customer_event) extends Omise_Event_Charge {};

		$this->assertFalse( $instance->validate() );
	}

	public function test_validate_returns_false_for_non_woocommerce_order() {
		$non_wc_charge_event = $this->mock_event_data( [ 'metadata' => null ] );

		$instance = new class($non_wc_charge_event) extends Omise_Event_Charge {};

		$this->assertFalse( $instance->validate() );
	}

	public function test_validate_returns_false_for_mismatched_transaction_id() {
		$charge_event = $this->mock_event_data();
		$order = Mockery::mock( 'WC_Order' );

		Monkey\Functions\expect( 'wc_get_order' )
			->with( '100' )
			->andReturn( $order );
		$order->shouldReceive( 'get_transaction_id' )
			->andReturn( 'chrg_test_12345' );

		$instance = new class($charge_event) extends Omise_Event_Charge {};

		$this->assertFalse( $instance->validate() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_validate_throws_error_if_unable_to_fetch_charge() {
		$charge_event = $this->mock_event_data();
		$order = Mockery::mock( 'WC_Order' );

		Monkey\Functions\expect( 'wc_get_order' )
			->with( '100' )
			->andReturn( $order );
		$order->shouldReceive( 'get_transaction_id' )
			->andReturn( 'chrg_test_no1t4tnemucod0e51mo' );
		$this->mockApiCall( 'omise-error-authentication-failure' );

		$this->expectException( exception: OmiseException::class );

		$instance = new class($charge_event) extends Omise_Event_Charge {};
		$instance->validate();
	}

	private function mock_event_data( array $event_data = [] ) {
		return array_replace_recursive( $this->base_event_data, $event_data );
	}
}
