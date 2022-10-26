<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Charge' ) ) {
	class Omise_Charge {
		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return boolean
		 */
		public static function is_authorized( $charge ) {
			return OmisePluginHelperCharge::isAuthorized( $charge );
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return boolean
		 */
		public static function is_paid( $charge ) {
			return OmisePluginHelperCharge::isPaid( $charge );
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return boolean
		 */
		public static function is_failed( $charge ) {
			return OmisePluginHelperCharge::isFailed( $charge );
		}

		/**
		 * @param OmiseCharge $charge  Omise's charge object
		 * @return string | boolean
		 */
		public static function get_error_message( $charge ) {
			if ( '' !== $charge['failure_code'] ) {
				return '(' . $charge['failure_code'] . ') ' . Omise()->translate( $charge['failure_message'] );
			}

			return '';
		}
	}
}