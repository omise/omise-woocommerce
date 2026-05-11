<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Feature_Flag {
	/**
	 * @param Omise_Payment $gateway
	 * @param WC_Order      $order
	 *
	 * @return bool
	 */
	public static function is_enabled_for_order( $gateway, $order ) {
		if ( ! self::is_supported_gateway( $gateway ) ) {
			return false;
		}

		$enabled = Omise_Setting::instance()->is_upa_enabled();

		return (bool) apply_filters( 'omise_upa_enabled_for_order', $enabled, $gateway, $order );
	}

	/**
	 * UPA currently supports Offsite and Offline Omise payment classes.
	 *
	 * @param mixed $gateway
	 *
	 * @return bool
	 */
	private static function is_supported_gateway( $gateway ) {
		return $gateway instanceof Omise_Payment_Offsite || $gateway instanceof Omise_Payment_Offline;
	}
}
