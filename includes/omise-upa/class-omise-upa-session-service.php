<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Session_Service {
	const META_SESSION_ID     = 'omise_upa_session_id';
	const META_STATE          = 'omise_upa_state';
	const META_PAYMENT_METHOD = 'omise_upa_payment_method';
	const META_FLOW           = 'omise_upa_flow';
	const META_RESOLVED       = 'omise_upa_resolved';

	const FLOW_OFFSITE = 'offsite';
	const FLOW_OFFLINE = 'offline';

	const COMPLETE_ENDPOINT = 'omise_upa_complete';
	const CANCEL_ENDPOINT   = 'omise_upa_cancel';

	/**
	 * @param Omise_Payment $gateway
	 * @param string|int    $order_id
	 * @param WC_Order      $order
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public static function create_checkout_session( $gateway, $order_id, $order ) {
		$source_type = Omise_UPA_Payment_Method_Resolver::resolve( $gateway );

		if ( empty( $source_type ) ) {
			if ( self::is_dynamic_source_gateway( $gateway ) ) {
				throw new Exception( __( 'Please select bank below', 'omise' ) );
			}

			throw new Exception( __( 'Payment service is temporarily unavailable. Please try again or choose another payment method.', 'omise' ) );
		}

		$state = Omise_UPA_State_Token::create();
		$client = self::create_client();

		$payload = self::build_payload( $order, $order_id, $source_type, $state );
		$session = $client->create_session( $payload );
		$session_id = self::extract_session_id( $session );

		if ( empty( $session_id ) || empty( $session['redirect_url'] ) ) {
			throw new Exception( __( 'Payment service is temporarily unavailable. Please try again or choose another payment method.', 'omise' ) );
		}

		$redirect_url = esc_url_raw( $session['redirect_url'] );
		self::validate_redirect_url( $redirect_url, $client->get_base_url() );

		Omise_UPA_State_Token::store( $order, $state );
		$order->update_meta_data( self::META_SESSION_ID, sanitize_text_field( $session_id ) );
		$order->update_meta_data( self::META_PAYMENT_METHOD, sanitize_text_field( $source_type ) );
		$order->update_meta_data( self::META_FLOW, self::resolve_flow( $gateway ) );
		$order->update_meta_data( self::META_RESOLVED, 'no' );
		$order->delete_meta_data( 'omise_upa_retry_attempts' );
		$order->save();

		$order->add_order_note(
			sprintf(
				__( 'Omise UPA: Redirecting buyer to %s', 'omise' ),
				esc_url( $redirect_url )
			)
		);

		return array(
			'result'   => 'success',
			'redirect' => $redirect_url,
		);
	}

	/**
	 * Gateways where customers must choose a bank source before creating UPA session.
	 *
	 * @param Omise_Payment $gateway
	 *
	 * @return bool
	 */
	private static function is_dynamic_source_gateway( $gateway ) {
		if ( ! isset( $gateway->id ) || ! is_string( $gateway->id ) ) {
			return false;
		}

		$dynamic_source_gateways = array(
			'omise_internetbanking',
			'omise_mobilebanking',
		);

		return in_array( $gateway->id, $dynamic_source_gateways, true );
	}

	/**
	 * @param Omise_Payment $gateway
	 *
	 * @return string
	 */
	private static function resolve_flow( $gateway ) {
		if ( $gateway instanceof Omise_Payment_Offline ) {
			return self::FLOW_OFFLINE;
		}

		return self::FLOW_OFFSITE;
	}

	/**
	 * @return Omise_UPA_Client
	 *
	 * @throws Exception
	 */
	private static function create_client() {
		$settings = Omise_Setting::instance();

		return new Omise_UPA_Client(
			$settings->get_upa_api_base_url(),
			$settings->secret_key()
		);
	}

	/**
	 * @param WC_Order $order
	 * @param string   $order_id
	 * @param string   $source_type
	 * @param string   $state
	 *
	 * @return array
	 */
	private static function build_payload( $order, $order_id, $source_type, $state ) {
		$currency = strtoupper( $order->get_currency() );
		$payload = array(
			'amount'          => Omise_Money::to_subunit( $order->get_total(), $currency ),
			'currency'        => $currency,
			'order_id'        => (string) $order_id,
			'description'     => 'WooCommerce Order id ' . $order_id,
			'payment_methods' => array( $source_type ),
			'redirect_urls'   => array(
				'complete_url' => self::build_callback_url( self::COMPLETE_ENDPOINT, $order_id, $state ),
				'cancel_url'   => self::build_callback_url( self::CANCEL_ENDPOINT, $order_id, $state ),
			),
			'metadata'        => array(
				'order_id'  => (string) $order_id,
				'order_key' => (string) $order->get_order_key(),
			),
		);

		$locale = substr( strtolower( get_locale() ), 0, 2 );
		if ( ! empty( $locale ) ) {
			$payload['locale'] = $locale;
		}

		return $payload;
	}

	/**
	 * @param string $endpoint
	 * @param string $order_id
	 * @param string $state
	 *
	 * @return string
	 */
	private static function build_callback_url( $endpoint, $order_id, $state ) {
		return add_query_arg(
			array(
				'wc-api'          => $endpoint,
				'order_id'        => $order_id,
				'omise_upa_state' => $state,
			),
			home_url( '/' )
		);
	}

	/**
	 * @param string $redirect_url
	 * @param string $base_url
	 *
	 * @throws Exception
	 */
	private static function validate_redirect_url( $redirect_url, $base_url ) {
		$redirect_host = parse_url( $redirect_url, PHP_URL_HOST );
		$base_host     = parse_url( $base_url, PHP_URL_HOST );

		if ( ! wp_http_validate_url( $redirect_url ) || empty( $redirect_host ) || empty( $base_host ) ) {
			throw new Exception( __( 'Payment service is temporarily unavailable. Please try again or choose another payment method.', 'omise' ) );
		}

		if ( 0 !== strcasecmp( $redirect_host, $base_host ) ) {
			throw new Exception( __( 'Payment service is temporarily unavailable. Please try again or choose another payment method.', 'omise' ) );
		}
	}

	/**
	 * Resolve session id from UPA response payload.
	 * UPA can return either "session_id" or "id" for the session identifier.
	 *
	 * @param array $session
	 *
	 * @return string
	 */
	private static function extract_session_id( $session ) {
		if ( isset( $session['session_id'] ) && is_string( $session['session_id'] ) ) {
			return sanitize_text_field( $session['session_id'] );
		}

		if ( isset( $session['id'] ) && is_string( $session['id'] ) ) {
			return sanitize_text_field( $session['id'] );
		}

		return '';
	}
}
