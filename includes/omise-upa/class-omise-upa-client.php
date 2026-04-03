<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Client {
	const CREATE_SESSION_TIMEOUT = 7;
	const GET_SESSION_TIMEOUT    = 5;
	const MAX_RETRIES            = 2;

	/**
	 * @var string
	 */
	private $base_url;

	/**
	 * @var string
	 */
	private $secret_key;

	/**
	 * @param string $base_url
	 * @param string $secret_key
	 *
	 * @throws Exception
	 */
	public function __construct( $base_url, $secret_key ) {
		$base_url = rtrim( trim( $base_url ), '/' );

		if ( empty( $base_url ) || ! wp_http_validate_url( $base_url ) ) {
			throw new Exception( __( 'Payment service is temporarily unavailable. Please try again later.', 'omise' ) );
		}

		if ( empty( $secret_key ) ) {
			throw new Exception( __( 'Payment service is temporarily unavailable. Please try again later.', 'omise' ) );
		}

		$this->base_url   = $base_url;
		$this->secret_key = $secret_key;
	}

	/**
	 * @param array $payload
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function create_session( $payload ) {
		return $this->request( 'POST', '/sessions', $payload, self::CREATE_SESSION_TIMEOUT );
	}

	/**
	 * @param string $session_id
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function get_session( $session_id ) {
		$session_id = rawurlencode( trim( $session_id ) );

		if ( empty( $session_id ) ) {
			throw new Exception( __( 'Payment service is temporarily unavailable. Please try again later.', 'omise' ) );
		}

		return $this->request( 'GET', '/sessions/' . $session_id, null, self::GET_SESSION_TIMEOUT );
	}

	/**
	 * @return string
	 */
	public function get_base_url() {
		return $this->base_url;
	}

	/**
	 * @param string   $method
	 * @param string   $path
	 * @param array|null $payload
	 * @param int      $timeout
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	private function request( $method, $path, $payload, $timeout ) {
		$url          = $this->base_url . $path;
		$max_attempts = 1 + self::MAX_RETRIES;

		for ( $attempt = 1; $attempt <= $max_attempts; $attempt++ ) {
			$args = array(
				'method'      => $method,
				'timeout'     => $timeout,
				'redirection' => 0,
				'headers'     => $this->build_headers( ! is_null( $payload ) ),
			);

			if ( ! is_null( $payload ) ) {
				$args['body'] = wp_json_encode( $payload );
			}

			$response = wp_remote_request( $url, $args );

			if ( is_wp_error( $response ) ) {
				$this->log_debug(
					'wp_error',
					array(
						'method'   => $method,
						'path'     => $path,
						'attempt'  => $attempt,
						'code'     => $response->get_error_code(),
						'message'  => $response->get_error_message(),
						'retrying' => ( $attempt < $max_attempts && $this->is_transient_wp_error( $response ) ),
					)
				);

				if ( $attempt < $max_attempts && $this->is_transient_wp_error( $response ) ) {
					$this->sleep_before_retry( $attempt );
					continue;
				}

				throw new Exception( $this->temporary_unavailable_message() );
			}

			$http_code = (int) wp_remote_retrieve_response_code( $response );
			$body      = wp_remote_retrieve_body( $response );

			if ( $http_code >= 200 && $http_code < 300 ) {
				return $this->decode_response_body( $body );
			}

			$is_retryable_status = 429 === $http_code || $http_code >= 500;
			$error_message       = $this->extract_error_message( $body, $http_code );

			$this->log_debug(
				'http_error',
				array(
					'method'        => $method,
					'path'          => $path,
					'attempt'       => $attempt,
					'http_code'     => $http_code,
					'error_message' => $error_message,
					'response_body' => $body,
					'retrying'      => ( $attempt < $max_attempts && $is_retryable_status ),
				)
			);

			if ( $attempt < $max_attempts && $is_retryable_status ) {
				$this->sleep_before_retry( $attempt );
				continue;
			}

			throw new Exception( $this->temporary_unavailable_message() );
		}

		throw new Exception( $this->temporary_unavailable_message() );
	}

	/**
	 * @param bool $include_content_type
	 *
	 * @return array
	 */
	private function build_headers( $include_content_type ) {
		$headers = array(
			'Accept'        => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( $this->secret_key . ':' ),
		);

		if ( defined( 'OMISE_PHP_LIB_VERSION' ) ) {
			$user_agent = 'OmisePHP/' . OMISE_PHP_LIB_VERSION . ' PHP/' . PHP_VERSION;

			if ( defined( 'OMISE_API_VERSION' ) ) {
				$user_agent .= ' OmiseAPI/' . OMISE_API_VERSION;
			}

			if ( defined( 'OMISE_USER_AGENT_SUFFIX' ) ) {
				$user_agent .= ' ' . OMISE_USER_AGENT_SUFFIX;
			}

			$headers['User-Agent'] = $user_agent;
		}

		if ( $include_content_type ) {
			$headers['Content-Type'] = 'application/json';
		}

		return $headers;
	}

	/**
	 * @param string $body
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	private function decode_response_body( $body ) {
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			throw new Exception( $this->temporary_unavailable_message() );
		}

		return $data;
	}

	/**
	 * @param WP_Error $error
	 *
	 * @return bool
	 */
	private function is_transient_wp_error( $error ) {
		$retryable_codes = array( 'http_request_failed', 'timeout', 'connect_timeout' );
		$code            = $error->get_error_code();
		$message         = strtolower( (string) $error->get_error_message() );

		if ( in_array( $code, $retryable_codes, true ) ) {
			return true;
		}

		$retryable_signals = array( 'timeout', 'timed out', 'temporarily', 'connection refused', 'connection reset', 'could not resolve host' );
		foreach ( $retryable_signals as $signal ) {
			if ( false !== strpos( $message, $signal ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $attempt
	 */
	private function sleep_before_retry( $attempt ) {
		$backoff_ms = (int) ( 200 * pow( 2, $attempt - 1 ) );
		$jitter_ms  = (int) wp_rand( 0, 120 );

		usleep( ( $backoff_ms + $jitter_ms ) * 1000 );
	}

	/**
	 * @param string $body
	 * @param int    $http_code
	 *
	 * @return string
	 */
	private function extract_error_message( $body, $http_code ) {
		$data = json_decode( $body, true );
		if ( is_array( $data ) && isset( $data['message'] ) && is_string( $data['message'] ) ) {
			return sanitize_text_field( $data['message'] );
		}

		return sprintf( 'UPA HTTP %d', $http_code );
	}

	/**
	 * @return string
	 */
	private function temporary_unavailable_message() {
		return __( 'Payment service is temporarily unavailable. Please try again or choose another payment method.', 'omise' );
	}

	/**
	 * Write debug logs only in debug mode.
	 *
	 * @param string $event
	 * @param array  $context
	 */
	private function log_debug( $event, $context ) {
		if ( ! defined( 'WP_DEBUG' ) || true !== WP_DEBUG ) {
			return;
		}

		$safe_context = array();

		foreach ( (array) $context as $key => $value ) {
			if ( 'response_body' === $key && is_string( $value ) ) {
				$value = $this->sanitize_response_body_for_log( $value );
			} else {
				$value = $this->sanitize_and_redact_context_value( $key, $value );
			}

			if ( 'response_body' === $key && is_string( $value ) ) {
				$value = substr( $value, 0, 500 );
			}

			$safe_context[ $key ] = $value;
		}

		error_log( 'Omise UPA client ' . sanitize_key( $event ) . ': ' . wp_json_encode( $safe_context ) );
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	private function sanitize_and_redact_context_value( $key, $value ) {
		if ( $this->is_sensitive_log_key( $key ) ) {
			return '[REDACTED]';
		}

		if ( is_array( $value ) ) {
			return $this->sanitize_and_redact_array( $value );
		}

		if ( is_object( $value ) ) {
			return $this->sanitize_and_redact_array( (array) $value );
		}

		if ( is_string( $value ) ) {
			return $this->redact_sensitive_fragments( sanitize_text_field( $value ) );
		}

		return $value;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	private function sanitize_and_redact_array( $data ) {
		$safe_data = array();

		foreach ( $data as $key => $value ) {
			if ( $this->is_sensitive_log_key( (string) $key ) ) {
				$safe_data[ $key ] = '[REDACTED]';
				continue;
			}

			if ( is_array( $value ) ) {
				$safe_data[ $key ] = $this->sanitize_and_redact_array( $value );
				continue;
			}

			if ( is_object( $value ) ) {
				$safe_data[ $key ] = $this->sanitize_and_redact_array( (array) $value );
				continue;
			}

			if ( is_string( $value ) ) {
				$safe_data[ $key ] = $this->redact_sensitive_fragments( sanitize_text_field( $value ) );
				continue;
			}

			$safe_data[ $key ] = $value;
		}

		return $safe_data;
	}

	/**
	 * @param string $body
	 *
	 * @return string
	 */
	private function sanitize_response_body_for_log( $body ) {
		$decoded_body = json_decode( $body, true );

		if ( is_array( $decoded_body ) ) {
			return wp_json_encode( $this->sanitize_and_redact_array( $decoded_body ) );
		}

		return $this->redact_sensitive_fragments( sanitize_text_field( $body ) );
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	private function redact_sensitive_fragments( $value ) {
		$patterns = array(
			'/\bBasic\s+[A-Za-z0-9+\/=]+\b/i',
			'/\bBearer\s+[A-Za-z0-9\-._~+\/]+=*\b/i',
			'/\bskey_(?:test|live)_[A-Za-z0-9_]+\b/i',
		);

		$redacted_value = preg_replace( $patterns, '[REDACTED]', $value );

		if ( null === $redacted_value ) {
			return $value;
		}

		return $redacted_value;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	private function is_sensitive_log_key( $key ) {
		$key = strtolower( (string) $key );

		if ( '' === $key ) {
			return false;
		}

		$sensitive_key_signals = array(
			'authorization',
			'secret',
			'skey',
			'api_key',
			'apikey',
			'token',
			'password',
		);

		foreach ( $sensitive_key_signals as $signal ) {
			if ( false !== strpos( $key, $signal ) ) {
				return true;
			}
		}

		return false;
	}
}
