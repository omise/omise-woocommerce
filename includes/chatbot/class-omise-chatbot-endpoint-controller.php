<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Endpoint_Controller' ) ) {
	return;
}

class Omise_Chatbot_Endpoint_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	const ENDPOINT_NAMESPACE = 'omise';

	/**
	 * @var string
	 */
	const ENDPOINT = 'chatbot';

	/**
	 * @var Omise_Setting
	 */
	protected $settings;

	/**
	 * @since 3.1
	 */
	public function __construct() {
		$this->settings = new Omise_Setting;
	}

	/**
	 * Register the routes for webhooks.
	 */
	public function register_routes() {
		register_rest_route(
			self::ENDPOINT_NAMESPACE,
			'/' . self::ENDPOINT . '/facebook',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'chatbot_facebook_verification' ),
				'permission_callback' => array( $this, 'chatbot_facebook_verification_permission_check' )
			)
		);
	}

	/**
	 * From Facebook's document:
	 * At your webhook URL, add code for verification. Your code should expect the Verify Token you
	 * previously defined, and respond with the challenge sent back in the verification request.
	 * Click the "Verify and Save" button in the New Page Subscription to call your webhook 
	 * with a GET request.
	 * 
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return void|WP_Error             Response object on success, or WP_Error object on failure.
	 *
	 * @see    https://developers.facebook.com/docs/messenger-platform/guides/setup/#verify_webhook
	 *
	 * @since  3.2
	 */
	public function chatbot_facebook_verification( $request ) {
		echo $request['hub_challenge'];

		die();
	}

	/**
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return true|WP_Error             True if the request has read access, otherwise WP_Error object.
	 *
	 * @since  3.2
	 */
	public function chatbot_facebook_verification_permission_check( $request ) {
		$settings = $this->settings->get_settings();

		if ( isset( $request['hub_challenge'] )
			 && isset( $request['hub_verify_token'] )
			 && $settings['chatbot_facebook_bot_verify_token'] === $request['hub_verify_token'] ) {
	 		return true;
	 	}

		return new WP_Error( 'omise_chatbot_failed_facebook_verification', __( 'Failed Facebook verification' ), array( 'status' => 400 ) );
	}
}
