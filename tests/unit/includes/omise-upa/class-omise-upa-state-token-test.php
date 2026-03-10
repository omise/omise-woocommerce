<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';

class Omise_UPA_State_Token_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-session-service.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-state-token.php';
	}

	public function test_create_generates_64_char_hex_state_token() {
		$state = Omise_UPA_State_Token::create();

		$this->assertIsString( $state );
		$this->assertSame( 64, strlen( $state ) );
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $state );
	}

	public function test_store_writes_state_meta_to_order() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'update_meta_data' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE, 'state_abc' );

		Omise_UPA_State_Token::store( $order, 'state_abc' );
		$this->assertTrue( true );
	}

	public function test_validate_returns_true_only_for_exact_state_match() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->times( 2 )
			->with( Omise_UPA_Session_Service::META_STATE )
			->andReturn( 'state_abc' );

		$this->assertTrue( Omise_UPA_State_Token::validate( $order, 'state_abc' ) );
		$this->assertFalse( Omise_UPA_State_Token::validate( $order, 'state_other' ) );
	}

	public function test_invalidate_removes_state_meta_from_order() {
		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'delete_meta_data' )
			->once()
			->with( Omise_UPA_Session_Service::META_STATE );

		Omise_UPA_State_Token::invalidate( $order );
		$this->assertTrue( true );
	}
}
