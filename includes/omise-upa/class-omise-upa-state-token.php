<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_State_Token {
	/**
	 * @return string
	 */
	public static function create() {
		return Token::random( 32 );
	}

	/**
	 * @param WC_Order $order
	 * @param string   $state
	 */
	public static function store( $order, $state ) {
		$order->update_meta_data( Omise_UPA_Session_Service::META_STATE, $state );
	}

	/**
	 * @param WC_Order    $order
	 * @param string|null $state
	 *
	 * @return bool
	 */
	public static function validate( $order, $state ) {
		$saved_state = $order->get_meta( Omise_UPA_Session_Service::META_STATE );

		if ( empty( $saved_state ) || empty( $state ) ) {
			return false;
		}

		return hash_equals( $saved_state, $state );
	}

	/**
	 * Invalidate the current callback state to prevent replay of the same
	 * callback URL after the order has reached a terminal state.
	 *
	 * @param WC_Order $order
	 */
	public static function invalidate( $order ) {
		$order->delete_meta_data( Omise_UPA_Session_Service::META_STATE );
	}
}
