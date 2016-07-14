<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Card_Image' ) ) {
	class Omise_Card_Image {

		private static function get_html_img($file, $alternate_text) {
			$url = WC_HTTPS::force_https_url ( WC()->plugin_url() . '/assets/images/icons/credit-cards/' );
			return "<img src='$url/$file' width='38px' alt='$alternate_text' />";
		}

		public static function get_amex_default_display() {
			return 'no';
		}

		public static function get_amex_image() {
			return self::get_html_img( 'amex.svg', 'American Express' );
		}

		public static function get_css() {
			return 'vertical-align: 5px;';
		}

		public static function get_diners_default_display() {
			return 'no';
		}

		public static function get_diners_image() {
			return self::get_html_img( 'diners.svg', 'Diners Club' );
		}

		public static function get_jcb_default_display() {
			return 'no';
		}

		public static function get_jcb_image() {
			return self::get_html_img( 'jcb.svg', 'JCB' );
		}

		public static function get_mastercard_default_display() {
			return 'yes';
		}

		public static function get_mastercard_image() {
			return self::get_html_img( 'mastercard.svg', 'MasterCard' );
		}

		public static function get_visa_default_display() {
			return 'yes';
		}

		public static function get_visa_image() {
			return self::get_html_img( 'visa.svg', 'Visa' );
		}

		public static function is_amex_enabled( $setting ) {
			if ( isset( $setting['accept_amex'] ) && $setting['accept_amex'] == 'yes' ) {
				return true;
			} else {
				return false;
			}
		}

		public static function is_diners_enabled( $setting ) {
			if ( isset( $setting['accept_diners'] ) && $setting['accept_diners'] == 'yes' ) {
				return true;
			} else {
				return false;
			}
		}

		public static function is_jcb_enabled( $setting ) {
			if ( isset( $setting['accept_jcb'] ) && $setting['accept_jcb'] == 'yes' ) {
				return true;
			} else {
				return false;
			}
		}

		public static function is_mastercard_enabled( $setting ) {
			if ( ! isset( $setting['accept_mastercard'] ) ) {
				return true;
			} else {
				if ( $setting['accept_mastercard'] == 'yes' ) {
					return true;
				} else {
					return false;
				}
			}
		}

		public static function is_visa_enabled( $setting ) {
			if ( ! isset( $setting['accept_visa'] ) ) {
				return true;
			} else {
				if ( $setting['accept_visa'] == 'yes' ) {
					return true;
				} else {
					return false;
				}
			}
		}
	}
}