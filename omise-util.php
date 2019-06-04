<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Util' ) ) {
	class Omise_Util {
		/**
		 * Renders error message in JSON format
		 * @param string $message
		 */
		public static function render_json_error( $message ) {
			echo json_encode( '{ "object": "error", "message": "' . $message . '" }' );
		}
	}
}
?>
