<?php
/**
 * Handles all the API response messages localization.
 *
 * @since 4.0
 */

defined( 'ABSPATH' ) || exit;

class Omise_Localization {
	/**
	 * @param  string $message
	 * @return string
	 */
	public static function translate( $message ) {
		$known_messages = array(
			'amount must be at least 200'
				=> __( 'amount must be at least 200', 'omise' ),

			'amount must be less than 50000'
				=> __( 'amount must be less than 50000', 'omise' ),

			'currency is currently not supported'
				=> __( 'currency is currently not supported', 'omise' ),

			'email is in invalid format'
				=> __( 'email is in invalid format', 'omise' ),

			'Metadata should be a JSON hash'
				=> __( 'Metadata should be a JSON hash', 'omise' ),

			'name cannot be blank'
				=> __( 'name cannot be blank', 'omise' ),

			'name cannot be blank, email is in invalid format, and phone_number must contain 10-11 digit characters'
				=> __( 'name cannot be blank, email is in invalid format, and phone_number must contain 10-11 digit characters', 'omise' ),

			'phone_number must contain 10-11 digit characters'
				=> __( 'phone_number must contain 10-11 digit characters', 'omise' ),

			'return uri is invalid'
				=> __( 'return uri is invalid', 'omise' ),

			'type is currently not supported'
				=> __( 'type is currently not supported', 'omise' ),
		);

		return isset( $known_messages[ $message ] ) ? $known_messages[ $message ] : $message;
	}
}
