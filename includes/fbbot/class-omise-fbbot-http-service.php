<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_HTTPService' ) ) {
  return;
}

class Omise_FBBot_HTTPService {
	private function __construct() { }

	public static function send_request( $url, $body ) {
		$data = array(
			'timeout' => 60,
			'body' => $body
		);

		$response = wp_safe_remote_post( $url, $data );

		return $response;
	}

	public static function send_delete_request( $url, $body ) {
		$defaults = array('method' => 'DELETE');

		$args = wp_parse_args( $body, $defaults );
    $response = wp_remote_request($url, $args);

    return $response;
	}
}