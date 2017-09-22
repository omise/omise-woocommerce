<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Facebook_Webhook_Event_Messages' ) ) {
	return;
}

class Omise_Chatbot_Facebook_Webhook_Event_Messages {
	/**
	 * @var string  of an event name.
	 */
	public $event = 'messages';

	/**
	 * @var Omise_Chatbot
	 */
	protected $chatbot;

	/**
	 * @since 3.2
	 */
	public function __construct() {
		$this->chatbot  = new Omise_Chatbot;
	}

	/**
	 * Note. It doesn't return anything back because nobody using the result
	 * unless we have a 'log' system.
	 *
	 * @param  mixed $messaging
	 *
	 * @return void
	 *
	 * @since 3.2
	 */
	public function handle( $messaging ) {
		return;
	}
}
