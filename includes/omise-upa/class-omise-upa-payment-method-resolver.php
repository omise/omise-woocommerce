<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Payment_Method_Resolver {
	const MOBILE_BANKING_GATEWAY_ID = 'omise_mobilebanking';
	const MOBILE_BANKING_METHOD     = 'mobile_banking';

	/**
	 * Resolve selected payment method for UPA session payload.
	 *
	 * @param Omise_Payment $gateway
	 *
	 * @return string
	 */
	public static function resolve( $gateway ) {
		$gateway_id = isset( $gateway->id ) ? $gateway->id : '';

		if ( self::MOBILE_BANKING_GATEWAY_ID === $gateway_id ) {
			return self::MOBILE_BANKING_METHOD;
		}

		$selected   = self::resolve_from_request( $gateway_id );

		if ( ! empty( $selected ) ) {
			return $selected;
		}

		$source_type = isset( $gateway->source_type ) ? $gateway->source_type : '';
		if ( ! is_string( $source_type ) ) {
			return '';
		}

		return sanitize_text_field( $source_type );
	}

	/**
	 * Resolve source type from checkout request payload.
	 *
	 * @param string $gateway_id
	 *
	 * @return string
	 */
	private static function resolve_from_request( $gateway_id ) {
		if ( ! in_array( $gateway_id, Omise_UPA_Session_Service::DYNAMIC_SOURCE_GATEWAYS, true ) ) {
			return '';
		}

		if ( ! isset( $_POST['omise-offsite'] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_POST['omise-offsite'] ) );
	}
}
