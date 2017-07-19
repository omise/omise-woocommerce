<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Configurator' ) ) {
  return;
}

class Omise_FBBot_Configurator {
	private static $version = '1';
	private static $namespace = 'omisemsgbot';
	private static $facebook_profile_endpoint = "https://graph.facebook.com/v2.6/me/messenger_profile?access_token=";

	public static function get_namespace() {
		return Omise_FBBot_Configurator::$namespace . '/v' . Omise_FBBot_Configurator::$version;
	}

	public static function get_fb_settings( $id ) {
		$option = get_option( 'woocommerce_omise_settings', null );
		return $option[$id];
	}

	public static function get_fb_profile_endpoint( $page_access_token ) {
		return Omise_FBBot_Configurator::$facebook_profile_endpoint . $page_access_token;
	}
}
