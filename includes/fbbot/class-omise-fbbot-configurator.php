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
}
