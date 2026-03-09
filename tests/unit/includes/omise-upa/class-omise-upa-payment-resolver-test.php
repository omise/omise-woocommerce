<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_UPA_Payment_Resolver_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-session-service.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-payment-resolver.php';
	}

	public function test_resolve_throws_exception_when_order_has_no_session_id() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_SESSION_ID )
			->andReturn( '' );

		$resolver = new Omise_UPA_Payment_Resolver( Mockery::mock() );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Unable to validate payment status. Please contact support.' );
		$resolver->resolve( $order );
	}

	public function test_resolve_returns_pending_when_session_has_no_payments() {
		$client = Mockery::mock();
		$client->shouldReceive( 'get_session' )
			->once()
			->with( 'sess_123' )
			->andReturn(
				array(
					'id'       => 'sess_123',
					'status'   => 'pending',
					'payments' => array(),
				)
			);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_SESSION_ID )
			->andReturn( 'sess_123' );

		$resolver = new Omise_UPA_Payment_Resolver( $client );
		$result   = $resolver->resolve( $order );

		$this->assertSame( Omise_UPA_Payment_Resolver::STATE_PENDING, $result['state'] );
		$this->assertNull( $result['charge'] );
		$this->assertNull( $result['payment'] );
	}

	public function test_resolve_prefers_successful_payment_and_returns_successful_state() {
		$client = Mockery::mock();
		$client->shouldReceive( 'get_session' )
			->once()
			->with( 'sess_456' )
			->andReturn(
				array(
					'id'     => 'sess_456',
					'status' => 'pending',
					'payments' => array(
						array(
							'charge_id'      => 'chrg_failed',
							'payment_method' => 'mobile_banking_kbank',
							'status'         => 'failed',
						),
						array(
							'charge_id'      => 'chrg_success',
							'payment_method' => 'mobile_banking_bbl',
							'status'         => 'successful',
						),
					),
				)
			);

		Mockery::mock( 'alias:OmiseCharge' )
			->shouldReceive( 'retrieve' )
			->once()
			->with( 'chrg_success' )
			->andReturn(
				array(
					'object'          => 'charge',
					'id'              => 'chrg_success',
					'status'          => 'successful',
					'paid'            => true,
					'authorized'      => false,
					'failure_code'    => null,
					'failure_message' => null,
				)
			);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_SESSION_ID )
			->andReturn( 'sess_456' );

		$resolver = new Omise_UPA_Payment_Resolver( $client );
		$result   = $resolver->resolve( $order );

		$this->assertSame( Omise_UPA_Payment_Resolver::STATE_SUCCESSFUL, $result['state'] );
		$this->assertSame( 'chrg_success', $result['payment']['charge_id'] );
		$this->assertSame( 'chrg_success', $result['charge']['id'] );
	}

	public function test_resolve_returns_failed_when_session_status_is_cancelled() {
		$client = Mockery::mock();
		$client->shouldReceive( 'get_session' )
			->once()
			->with( 'sess_789' )
			->andReturn(
				array(
					'id'     => 'sess_789',
					'status' => 'cancelled',
					'payments' => array(
						array(
							'charge_id'      => 'chrg_789',
							'payment_method' => 'promptpay',
							'status'         => 'pending',
						),
					),
				)
			);

		Mockery::mock( 'alias:OmiseCharge' )
			->shouldReceive( 'retrieve' )
			->once()
			->with( 'chrg_789' )
			->andReturn(
				array(
					'object'          => 'charge',
					'id'              => 'chrg_789',
					'status'          => 'pending',
					'paid'            => false,
					'authorized'      => false,
					'failure_code'    => null,
					'failure_message' => null,
				)
			);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_SESSION_ID )
			->andReturn( 'sess_789' );

		$resolver = new Omise_UPA_Payment_Resolver( $client );
		$result   = $resolver->resolve( $order );

		$this->assertSame( Omise_UPA_Payment_Resolver::STATE_FAILED, $result['state'] );
		$this->assertSame( 'chrg_789', $result['charge']['id'] );
	}
}
