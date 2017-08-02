<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_HTTPService' ) ) {
	return;
}

class Omise_FBBot_HTTPService {
	private function __construct() { }

	public static function send_request( $url, $body ) {
		return wp_safe_remote_post( $url, array(
				'timeout' => 60,
				'body' => $body
			) 
		);
	}

	public static function send_delete_request( $url, $body ) {
		$defaults = array('method' => 'DELETE');

		$args = wp_parse_args( $body, $defaults );
		$response = wp_remote_request($url, $args);

		return $response;
	}

	public static function send_get_request( $url, $body = array() ) {
		return wp_safe_remote_get( $url, array(
				'timeout' => 60,
				'body' => $body
			) 
		);
	}

	public static function send_message_to( $receiver_id, $message ) {
		$url = Omise_FBBot_Configurator::get_fb_message_endpoint();

		return self::send_request( $url, array(
				'recipient' => array('id' => $receiver_id),
				'message' => $message
			)
		);
	}
}