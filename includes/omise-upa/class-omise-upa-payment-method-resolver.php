<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Payment_Method_Resolver {
	/**
	 * Resolve selected payment method for UPA session payload.
	 *
	 * @param Omise_Payment $gateway
	 *
	 * @return string
	 */
	public static function resolve( $gateway ) {
		$gateway_id = isset( $gateway->id ) ? $gateway->id : '';
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
		$dynamic_source_gateways = array( 'omise_internetbanking', 'omise_mobilebanking' );

		if ( ! in_array( $gateway_id, $dynamic_source_gateways, true ) ) {
			return '';
		}

		if ( ! isset( $_POST['omise-offsite'] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_POST['omise-offsite'] ) );
	}
}
