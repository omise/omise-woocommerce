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
			else if ( $_SERVER['REMOTE_ADDR'] )
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
			// TODO: Make it to not relate with `admin` directory.
			require_once( plugin_dir_path( __FILE__ ) . 'includes/admin/views/partials/' . $partial_name . '-template.php' );
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
	}
}
?>
