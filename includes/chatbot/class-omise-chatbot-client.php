<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Client' ) ) {
	return;
}

class Omise_Chatbot_Client {
	/**
	 * @param  string $url
	 * @param  mixed  $body
	 *
	 * @return WP_Error|array  The response or WP_Error on failure.
	 *
	 * @since  3.2
	 */
	public function get( $url, $body = null ) {
		return wp_safe_remote_get(
			$url,
			array(
				'timeout' => 60,
				'body'    => $body
			) 
		);
	}

	/**
	 * @param  string $url
	 * @param  mixed  $body
	 *
	 * @return WP_Error|array  The response or WP_Error on failure.
	 *
	 * @since  3.2
	 */
	public function post( $url, $body = null ) {
		return wp_safe_remote_post(
			$url,
			array(
				'timeout' => 60,
				'body'    => $body
			) 
		);
	}

	/**
	 * @param  string $url
	 * @param  mixed  $body
	 *
	 * @return WP_Error|array  The response or WP_Error on failure.
	 *
	 * @since  3.2
	 */
	public function delete( $url, $body = null ) {
		return wp_remote_request(
			$url,
			wp_parse_args(
				$body,
				array( 'method' => 'DELETE' )
			)
		);
	}
}
