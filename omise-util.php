<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Util' ) ) {
	class Omise_Util {
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
		 * Outputs platform type of IOS, ANDROID or null for our source API.
		 * @param string $userAgent (normally $_SERVER['HTTP_USER_AGENT'])
		 */
		public static function get_platform_type( $userAgent ) {
			if ( preg_match( "/(Android)/i", $userAgent ) ) {
				return "ANDROID";
			}
			
			if ( preg_match( "/(iPad|iPhone|iPod)/i", $userAgent ) ) {
				return 'IOS';
			}

			return null;
		}

		/**
		 * Check if current platform is mobile or not
		 */
		public static function isMobilePlatform()
		{
			return null !== self::get_platform_type(wc_get_user_agent());
		}

		public static function get_webhook_url()
		{
			return get_rest_url( null, 'omise/webhooks' );
		}
	}
}
