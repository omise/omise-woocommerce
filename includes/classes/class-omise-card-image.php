<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Card_Image' ) ) {
	class Omise_Card_Image {
		/**
		 * Compose the given parameters into the string of HTML &lt;img&gt; element
		 *
		 * @param string $file Image file name with extension such as image.jpg
		 * @param string $alternate_text Alternate text for the image
		 * @return string HTML &lt;img&gt; element
		 */
		private static function get_image( $file, $alternate_text ) {
			$url = WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/' );
			return "<img src='$url/$file' class='Omise-CardBrandImage' style='width: 38px;' alt='$alternate_text' />";
		}

		/**
		 * Return the default setting of display the American Express logo
		 *
		 * @return string
		 */
		public static function get_amex_default_display() {
			return 'no';
		}

		/**
		 * Return the HTML &lt;img&gt; element of American Express logo
		 *
		 * @return string
		 */
		public static function get_amex_image() {
			return self::get_image( 'amex.svg', 'American Express' );
		}

		/**
		 * Return the CSS used to format the image to be displayed vertical center align with checkbox
		 * at the back-end setting page
		 *
		 * @return string
		 */
		public static function get_css() {
			return 'vertical-align: 5px;';
		}

		/**
		 * Return the default setting of display the Diners Club logo
		 *
		 * @return string
		 */
		public static function get_diners_default_display() {
			return 'no';
		}

		/**
		 * Return the HTML &lt;img&gt; element of Diners Club logo
		 *
		 * @return string
		 */
		public static function get_diners_image() {
			return self::get_image( 'diners.svg', 'Diners Club' );
		}

		/**
		 * Return the default setting of display the JCB logo
		 *
		 * @return string
		 */
		public static function get_jcb_default_display() {
			return 'no';
		}

		/**
		 * Return the HTML &lt;img&gt; element of JCB logo
		 *
		 * @return string
		 */
		public static function get_jcb_image() {
			return self::get_image( 'jcb.svg', 'JCB' );
		}

		/**
		 * Return the default setting of display the MasterCard logo
		 *
		 * @return string
		 */
		public static function get_mastercard_default_display() {
			return 'yes';
		}

		/**
		 * Return the HTML &lt;img&gt; element of MasterCard logo
		 *
		 * @return string
		 */
		public static function get_mastercard_image() {
			return self::get_image( 'mastercard.svg', 'MasterCard' );
		}

		/**
		 * Return the default setting of display the Visa logo
		 *
		 * @return string
		 */
		public static function get_visa_default_display() {
			return 'yes';
		}

		/**
		 * Return the HTML &lt;img&gt; element of Visa logo
		 *
		 * @return string
		 */
		 public static function get_visa_image() {
			return self::get_image( 'visa.svg', 'Visa' );
		}

		/**
		 * Check whether the setting for American Express logo is configured and it was set to display or not display
		 *
		 * @param mixed $setting The array that contains key for checking the flag
		 * @return boolean
		 */
		public static function is_amex_enabled( $setting ) {
			if ( isset( $setting['accept_amex'] ) && $setting['accept_amex'] == 'yes' ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether the setting for Diners Club logo is configured and it was set to display or not display
		 *
		 * @param mixed $setting The array that contains key for checking the flag
		 * @return boolean
		 */
		public static function is_diners_enabled( $setting ) {
			if ( isset( $setting['accept_diners'] ) && $setting['accept_diners'] == 'yes' ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether the setting for JCB logo is configured and it was set to display or not display
		 *
		 * @param mixed $setting The array that contains key for checking the flag
		 * @return boolean
		 */
		public static function is_jcb_enabled( $setting ) {
			if ( isset( $setting['accept_jcb'] ) && $setting['accept_jcb'] == 'yes' ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether the setting for MasterCard logo is configured and it was set to display or not display
		 *
		 * @param mixed $setting The array that contains key for checking the flag
		 * @return boolean
		 */
		public static function is_mastercard_enabled( $setting ) {
			// Make it backward compatible. If the setting is not configured, the MasterCard logo is display by default.
			if ( ! isset( $setting['accept_mastercard'] ) ) {
				return self::get_mastercard_default_display();
			}

			if ( $setting['accept_mastercard'] == 'yes' ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether the setting for Visa logo is configured and it was set to display or not display
		 *
		 * @param mixed $setting The array that contains key for checking the flag
		 * @return boolean
		 */
		public static function is_visa_enabled( $setting ) {
			// Make it backward compatible. If the setting is not configured, the Visa logo is display by default.
			if ( ! isset( $setting['accept_visa'] ) ) {
				return self::get_visa_default_display();
			}

			if ( $setting['accept_visa'] == 'yes' ) {
				return true;
			}

			return false;
		}
	}
}