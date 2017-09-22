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
	 * @var Omise_Chatbot
	 */
	protected $chatbot;

	/**
	 * @var Omise_Chatbot_Facebook_Webhook_Events
	 */
	protected $event;

	/**
	 * @var Omise_Setting
	 */
	protected $settings;

	/**
	 * @since 3.1
	 */
	public function __construct() {
		$this->chatbot  = new Omise_Chatbot;
		$this->event    = new Omise_Chatbot_Facebook_Webhook_Events;
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
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'chatbot_facebook_verification' ),
					'permission_callback' => array( $this, 'chatbot_facebook_verification_permission_check' )
				),

				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'chatbot_facebook_response' ),
					'permission_callback' => array( $this, 'chatbot_facebook_response_permission_check' )
				)
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

	/**
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return true|WP_Error             True if the request has read access, otherwise WP_Error object.
	 *
	 * @see    https://developers.facebook.com/docs/messenger-platform/webhook
	 *
	 * @since  3.2
	 */
	public function chatbot_facebook_response( $request ) {
		foreach ( $request['entry'] as $entry ) {
			// Note, Facebook document says "even though this is an array, it will only contain one messaging object.".
			// Ref. https://developers.facebook.com/docs/messenger-platform/webhook
			$this->event->handle( $entry['messaging'][0] );
		}
	}

	/**
	 * @param  WP_REST_Request $request  Full details about the request.
	 *
	 * @return true|WP_Error             True if the request has read access, otherwise WP_Error object.
	 *
	 * @see    https://developers.facebook.com/docs/messenger-platform/webhook
	 *
	 * @since  3.2
	 */
	public function chatbot_facebook_response_permission_check( $request ) {
		if ( ! isset( $request['object'] ) || 'page' !== $request['object'] ) {
			return new WP_Error( 'omise_chatbot_failed_facebook_webhook', __( 'Attribute "object" must exist and its value must be "page"' ), array( 'status' => 400 ) );
		}

		if ( ! isset( $request['entry'] ) || empty( $request['entry'] ) ) {
			return new WP_Error( 'omise_chatbot_failed_facebook_webhook', __( 'Attribute "entry" must exist and cannot be empty' ), array( 'status' => 400 ) );
		}

		if ( ! isset( $request['entry'][0]['messaging'] ) || empty( $request['entry'][0]['messaging'] ) ) {
			return new WP_Error( 'omise_chatbot_failed_facebook_webhook', __( 'Attribute "messaging" must exist and cannot be empty' ), array( 'status' => 400 ) );
		}

		return true;
	}
}
