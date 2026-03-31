<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Payment_Method_Resolver {
	const MOBILE_BANKING_METHOD = 'mobile_banking';

	/**
	 * Resolve selected payment method for UPA session payload.
	 *
	 * @param Omise_Payment $gateway
	 *
	 * @return string
	 */
	public static function resolve( $gateway ) {
		$gateway_id = isset( $gateway->id ) ? $gateway->id : '';

		if ( Omise_Payment_Mobilebanking::ID === $gateway_id ) {
			return self::MOBILE_BANKING_METHOD;
		}

		$source_type = isset( $gateway->source_type ) ? $gateway->source_type : '';
		if ( ! is_string( $source_type ) ) {
			return '';
		}

		return sanitize_text_field( $source_type );
	}
}
