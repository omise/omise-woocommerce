<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Facebook_Webhook_Events' ) ) {
	return;
}

class Omise_Chatbot_Facebook_Webhook_Events {
	/**
	 * @var array  of event classes that Chatbot can handle.
	 */
	protected $events = array();

	public function __construct() {
		$events = array(
			'Omise_Chatbot_Facebook_Webhook_Event_Messages',
			'Omise_Chatbot_Facebook_Webhook_Event_Messaging_Postbacks',
		);

		foreach ( $events as $event ) {
			$clazz = new $event;
			$this->events[ $clazz->event ] = $clazz;
		}
	}

	/**
	 * Note. It doesn't return anything back because nobody using the result
	 * unless we have a 'log' system.
	 *
	 * @param  mixed $messaging
	 *
	 * @return void
	 */
	public function handle( $messaging ) {
		/**
		 * Subscribes to Message Received events
		 *
		 * @see https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/message
		 */
		if ( isset( $messaging['message'] ) ) {
			$this->events['messages']->handle( $messaging );

		/**
		 * Postback Received events
		 *
		 * @see https://developers.facebook.com/docs/messenger-platform/reference/webhook-events/messaging_postbacks
		 */
		} else if ( isset( $messaging['postback'] ) ) {
			$this->events['messaging_postbacks']->handle( $messaging );
		}
	}
}
