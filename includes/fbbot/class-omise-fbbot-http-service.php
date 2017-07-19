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
}