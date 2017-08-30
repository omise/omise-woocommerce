<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Configurator' ) ) {
	return;
}

class Omise_FBBot_Configurator {
	private static $version = '1';
	private static $namespace = 'omisemsgbot';
	private static $facebook_profile_endpoint = "https://graph.facebook.com/v2.6/me/messenger_profile?access_token=";
	private static $facebook_message_endpoint = "https://graph.facebook.com/v2.6/me/messages?access_token=";

	public static function get_namespace() {
		return self::$namespace . '/v' . self::$version;
	}

	public static function get_fb_settings( $id ) {
		$option = get_option( 'woocommerce_omise_settings', null );
		return $option[$id];
	}

	public static function get_fb_profile_endpoint() {
		$page_access_token = self::get_fb_settings( 'facebook_page_access_token' );
		return self::$facebook_profile_endpoint . $page_access_token;
	}

	public static function get_fb_message_endpoint() {
		$page_access_token = self::get_fb_settings( 'facebook_page_access_token' );
		return self::$facebook_message_endpoint . $page_access_token;
	}

	public static function facebook_bot_is_enable() {
		$is_enable = self::get_fb_settings('omise_facebook_bot') == 'yes';
		if ( ! $is_enable ) {
			return false;
		}

		$time_zone_code = get_option('timezone_string');
		if ( ! $time_zone_code ) {
			error_log('Time zone code is null. please set in setting page first');
			// Note: Bot still working if user not select timezone.
			return true;
		}

		$available_time_from = self::get_fb_settings('facebook_bot_available_time_from');
		$available_time_to = self::get_fb_settings('facebook_bot_available_time_to');

		try {
			$time_zone 			= new DateTimeZone($time_zone_code);
			$current_datetime 	= new DateTime('now', $time_zone);
			$from_datetime 		= new DateTime($available_time_from, $time_zone);
			$to_datetime 		= new DateTime($available_time_to, $time_zone);

			return ($current_datetime > $from_datetime && $current_datetime < $to_datetime);
		} catch(Exception $err) {
			error_log('Failed to parse time string');
			// Note: Bot still working if user input the wrong time format.
			return true;
		}
	}
}
