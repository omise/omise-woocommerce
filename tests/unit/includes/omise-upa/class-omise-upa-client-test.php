<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_UPA_Client_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-client.php';

		Monkey\Functions\stubs(
			array(
				'sanitize_text_field' => function( $value ) {
					return $value;
				},
				'sanitize_key' => function( $value ) {
					return $value;
				},
				'wp_json_encode' => function( $data ) {
					return json_encode( $data );
				},
			)
		);
	}

	// ─── Constructor Tests ──────────────────────────────────────────────

	public function test_constructor_throws_when_base_url_is_empty() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( false );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again later.' );

		new Omise_UPA_Client( '', 'skey_test_123' );
	}

	public function test_constructor_throws_when_base_url_is_invalid() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->once()->with( 'not-a-url' )->andReturn( false );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again later.' );

		new Omise_UPA_Client( 'not-a-url', 'skey_test_123' );
	}

	public function test_constructor_throws_when_secret_key_is_empty() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again later.' );

		new Omise_UPA_Client( 'https://upa.example.com', '' );
	}

	public function test_constructor_accepts_valid_params() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com/', 'skey_test_123' );

		$this->assertSame( 'https://upa.example.com', $client->get_base_url() );
	}

	public function test_constructor_trims_trailing_slash() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( '  https://upa.example.com///  ', 'skey_test_123' );

		$this->assertSame( 'https://upa.example.com', $client->get_base_url() );
	}

	// ─── create_session Tests ───────────────────────────────────────────

	public function test_create_session_returns_decoded_response_on_success() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$expected = array( 'session_id' => 'sess_123', 'redirect_url' => 'https://upa.example.com/pay' );

		Monkey\Functions\expect( 'wp_remote_request' )
			->once()
			->andReturn( array( 'response' => array( 'code' => 200 ), 'body' => json_encode( $expected ) ) );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )->once()->andReturn( 200 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )->once()->andReturn( json_encode( $expected ) );
		Monkey\Functions\expect( 'is_wp_error' )->once()->andReturn( false );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$result = $client->create_session( array( 'amount' => 1000 ) );

		$this->assertSame( $expected, $result );
	}

	public function test_create_session_throws_on_non_json_response() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		Monkey\Functions\expect( 'wp_remote_request' )->once()->andReturn( array() );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )->once()->andReturn( 200 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )->once()->andReturn( 'not json' );
		Monkey\Functions\expect( 'is_wp_error' )->once()->andReturn( false );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again or choose another payment method.' );

		$client->create_session( array( 'amount' => 1000 ) );
	}

	// ─── get_session Tests ──────────────────────────────────────────────

	public function test_get_session_returns_decoded_response() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$expected = array( 'id' => 'sess_123', 'status' => 'completed' );

		Monkey\Functions\expect( 'wp_remote_request' )->once()->andReturn( array() );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )->once()->andReturn( 200 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )->once()->andReturn( json_encode( $expected ) );
		Monkey\Functions\expect( 'is_wp_error' )->once()->andReturn( false );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$result = $client->get_session( 'sess_123' );

		$this->assertSame( $expected, $result );
	}

	public function test_get_session_throws_when_session_id_is_empty() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Payment service is temporarily unavailable. Please try again later.' );

		$client->get_session( '' );
	}

	// ─── HTTP Error / Retry Tests ───────────────────────────────────────

	public function test_request_throws_on_non_retryable_http_error() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		Monkey\Functions\expect( 'wp_remote_request' )->once()->andReturn( array() );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )->once()->andReturn( 400 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )->once()->andReturn( '{"message":"bad request"}' );
		Monkey\Functions\expect( 'is_wp_error' )->once()->andReturn( false );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );

		$this->expectException( Exception::class );

		$client->create_session( array( 'amount' => 1000 ) );
	}

	public function test_request_retries_on_500_error_then_succeeds() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'wp_rand' )->andReturn( 0 );

		$expected = array( 'session_id' => 'sess_ok' );

		// First call: 500 error
		// Second call: success
		Monkey\Functions\expect( 'wp_remote_request' )
			->twice()
			->andReturn( array(), array() );
		Monkey\Functions\expect( 'is_wp_error' )
			->twice()
			->andReturn( false );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )
			->twice()
			->andReturn( 500, 200 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )
			->twice()
			->andReturn( '{"message":"server error"}', json_encode( $expected ) );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$result = $client->create_session( array( 'amount' => 1000 ) );

		$this->assertSame( $expected, $result );
	}

	public function test_request_retries_on_429_then_succeeds() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'wp_rand' )->andReturn( 0 );

		$expected = array( 'session_id' => 'sess_ok' );

		Monkey\Functions\expect( 'wp_remote_request' )
			->twice()
			->andReturn( array(), array() );
		Monkey\Functions\expect( 'is_wp_error' )
			->twice()
			->andReturn( false );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )
			->twice()
			->andReturn( 429, 200 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )
			->twice()
			->andReturn( '{"message":"rate limited"}', json_encode( $expected ) );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$result = $client->create_session( array( 'amount' => 1000 ) );

		$this->assertSame( $expected, $result );
	}

	public function test_request_throws_after_max_retries_exhausted() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'wp_rand' )->andReturn( 0 );

		// 3 attempts (1 + MAX_RETRIES=2), all 500
		Monkey\Functions\expect( 'wp_remote_request' )
			->times( 3 )
			->andReturn( array() );
		Monkey\Functions\expect( 'is_wp_error' )
			->times( 3 )
			->andReturn( false );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )
			->times( 3 )
			->andReturn( 500 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )
			->times( 3 )
			->andReturn( '{"message":"server error"}' );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );

		$this->expectException( Exception::class );

		$client->create_session( array( 'amount' => 1000 ) );
	}

	// ─── WP_Error Tests ─────────────────────────────────────────────────

	public function test_request_retries_on_transient_wp_error() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );
		Monkey\Functions\expect( 'wp_rand' )->andReturn( 0 );

		$wp_error = Mockery::mock( 'WP_Error' );
		$wp_error->shouldReceive( 'get_error_code' )->andReturn( 'http_request_failed' );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'connection timeout' );

		$expected = array( 'session_id' => 'sess_ok' );

		Monkey\Functions\expect( 'wp_remote_request' )
			->twice()
			->andReturn( $wp_error, array() );
		Monkey\Functions\expect( 'is_wp_error' )
			->twice()
			->andReturn( true, false );
		Monkey\Functions\expect( 'wp_remote_retrieve_response_code' )
			->once()
			->andReturn( 200 );
		Monkey\Functions\expect( 'wp_remote_retrieve_body' )
			->once()
			->andReturn( json_encode( $expected ) );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$result = $client->create_session( array( 'amount' => 1000 ) );

		$this->assertSame( $expected, $result );
	}

	public function test_request_throws_on_non_transient_wp_error() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$wp_error = Mockery::mock( 'WP_Error' );
		$wp_error->shouldReceive( 'get_error_code' )->andReturn( 'ssl_verification_failed' );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'SSL certificate problem' );

		Monkey\Functions\expect( 'wp_remote_request' )
			->once()
			->andReturn( $wp_error );
		Monkey\Functions\expect( 'is_wp_error' )
			->once()
			->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );

		$this->expectException( Exception::class );

		$client->create_session( array( 'amount' => 1000 ) );
	}

	// ─── Private Method Coverage via Reflection ─────────────────────────

	public function test_is_transient_wp_error_detects_timeout_code() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'is_transient_wp_error' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$wp_error = Mockery::mock( 'WP_Error' );
		$wp_error->shouldReceive( 'get_error_code' )->andReturn( 'timeout' );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( '' );

		$this->assertTrue( $method->invoke( $client, $wp_error ) );
	}

	public function test_is_transient_wp_error_detects_retryable_signal_in_message() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'is_transient_wp_error' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$wp_error = Mockery::mock( 'WP_Error' );
		$wp_error->shouldReceive( 'get_error_code' )->andReturn( 'unknown' );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'Connection refused by host' );

		$this->assertTrue( $method->invoke( $client, $wp_error ) );
	}

	public function test_is_transient_wp_error_returns_false_for_non_retryable() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'is_transient_wp_error' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$wp_error = Mockery::mock( 'WP_Error' );
		$wp_error->shouldReceive( 'get_error_code' )->andReturn( 'ssl_verification_failed' );
		$wp_error->shouldReceive( 'get_error_message' )->andReturn( 'SSL certificate problem' );

		$this->assertFalse( $method->invoke( $client, $wp_error ) );
	}

	public function test_extract_error_message_returns_message_from_json() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'extract_error_message' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$result = $method->invoke( $client, '{"message":"insufficient funds"}', 422 );
		$this->assertSame( 'insufficient funds', $result );
	}

	public function test_extract_error_message_returns_fallback_for_non_json() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'extract_error_message' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$result = $method->invoke( $client, 'not json', 500 );
		$this->assertSame( 'UPA HTTP 500', $result );
	}

	public function test_build_headers_includes_content_type_when_payload_present() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'build_headers' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$headers = $method->invoke( $client, true );
		$this->assertArrayHasKey( 'Content-Type', $headers );
		$this->assertSame( 'application/json', $headers['Content-Type'] );
		$this->assertArrayHasKey( 'Authorization', $headers );
		$this->assertStringStartsWith( 'Basic ', $headers['Authorization'] );
	}

	public function test_build_headers_excludes_content_type_for_get_requests() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'build_headers' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$headers = $method->invoke( $client, false );
		$this->assertArrayNotHasKey( 'Content-Type', $headers );
		$this->assertArrayHasKey( 'Accept', $headers );
	}

	public function test_decode_response_body_throws_on_non_array() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'decode_response_body' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		$this->expectException( Exception::class );
		$method->invoke( $client, '"just a string"' );
	}

	public function test_log_debug_does_nothing_when_wp_debug_not_defined() {
		Monkey\Functions\expect( 'wp_http_validate_url' )->andReturn( true );

		$client = new Omise_UPA_Client( 'https://upa.example.com', 'skey_test_123' );
		$method = new ReflectionMethod( Omise_UPA_Client::class, 'log_debug' );
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		// Should not throw - WP_DEBUG is not defined in test env so it should just return
		$method->invoke( $client, 'test_event', array( 'key' => 'value' ) );
		$this->assertTrue( true ); // No exception means it passed
	}
}
