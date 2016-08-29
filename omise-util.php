<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Util' ) ) {
	class Omise_Util {
		/**
		 * Get Ip Address of client
		 * @return string
		 */
		public static function get_client_ip() {
			$ipaddress = '';

			if ( $_SERVER['HTTP_CLIENT_IP'] )
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			else if ( $_SERVER['HTTP_X_FORWARDED_FOR'] )
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else if ( $_SERVER['HTTP_X_FORWARDED'] )
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			else if ( $_SERVER['HTTP_FORWARDED_FOR'] )
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			else if ( $_SERVER['HTTP_FORWARDED'] )
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			else if ($_SERVER['REMOTE_ADDR'] )
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			else
				$ipaddress = 'UNKNOWN';
			return $ipaddress;
		}

		/**
		 * Renders php template
		 * @param string $viewPath
		 * @param Array $viewData
		 */
		public static function render_view( $viewPath, $viewData ) {
			require_once( plugin_dir_path( __FILE__ ) . $viewPath );
		}

		/**
		 * Renders error message in JSON format
		 * @param string $message
		 */
		public static function render_json_error( $message ) {
			echo json_encode( '{ "object": "error", "message": "' . $message . '" }' );
		}

		/**
		 * @param string $partial_name
		 * @param mixed  $partial_data
		 * @return void
		 */
		public static function render_partial( $partial_name, $partial_data = null ) {
			require_once( plugin_dir_path( __FILE__ ) . 'includes/templates/partials/' . $partial_name . '-template.php' );
		}

		/**
		 * @return string
		 */
		public static function date_format( $date, $format = null ) {
			$gmt_offset = get_option( 'gmt_offset' );
			$datetime   = new \DateTime( $date );

			if ( $gmt_offset > 0 ) {
				$gmt_offset = $gmt_offset * 60;
				$datetime->add( new \DateInterval( 'PT' . $gmt_offset . 'M' ) );
			} else if ( $gmt_offset < 0 ) {
				$gmt_offset = substr( $gmt_offset, 1 ) * 60;
				$datetime->sub( new \DateInterval( 'PT' . $gmt_offset . 'M' ) );
			}

			return $datetime->format( 'F d, Y H:i' );
		}

		/**
		 * Translate the given text for text domain, omise-woocommerce
		 * 
		 * To translate the text for WordPress, it need to define the text domain for each plugins.
		 * This function wraps the text domain to prevent the spreading text domain throughout the source code.
		 * 
		 * @param string $text The text to translate
		 * @param string $context The text that used to distinguish the same words but those words will be displayed in the different context
		 * @return string
		 */
		public static function translate( $text, $context = '' ) {
			$text_domain = 'omise-woocommerce';
			
			if ( empty( $context ) ) {
				return __( $text, $text_domain );
			} else {
				return _x( $text, $context, $text_domain );
			}
		}
	}
}
?>
