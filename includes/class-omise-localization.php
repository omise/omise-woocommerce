<?php

defined( 'ABSPATH' ) || exit;

/**
 * Handles all the API response messages localization.
 *
 * @since 4.1
 */
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

			'card is stolen or lost'
				=> __( 'card is stolen or lost', 'omise' ),

			'currency is currently not supported'
				=> __( 'currency is currently not supported', 'omise' ),

			'email is in invalid format'
				=> __( 'email is in invalid format', 'omise' ),

			'failed fraud check'
				=> __( 'failed fraud check', 'omise' ),

			'failed processing'
				=> __( 'failed processing', 'omise' ),

			'insufficient funds in the account or the card has reached the credit limit'
				=> __( 'insufficient funds in the account or the card has reached the credit limit', 'omise' ),

			'Metadata should be a JSON hash'
				=> __( 'Metadata should be a JSON hash', 'omise' ),

			'name cannot be blank'
				=> __( 'name cannot be blank', 'omise' ),

			'name cannot be blank, email is in invalid format, and phone_number must contain 10-11 digit characters'
				=> __( 'name cannot be blank, email is in invalid format, and phone_number must contain 10-11 digit characters', 'omise' ),

			'payment rejected'
				=> __( 'payment rejected', 'omise' ),

			'phone_number must contain 10-11 digit characters'
				=> __( 'phone_number must contain 10-11 digit characters', 'omise' ),

			'return uri is invalid'
				=> __( 'return uri is invalid', 'omise' ),

			'the account number is invalid'
				=> __( 'the account number is invalid', 'omise' ),

			'the security code is invalid'
				=> __( 'the security code is invalid', 'omise' ),

			'type is currently not supported'
				=> __( 'type is currently not supported', 'omise' ),
		);

		return isset( $known_messages[ $message ] ) ? $known_messages[ $message ] : $message;
	}
}
