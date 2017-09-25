<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Facebook_Webhook_Event_Messaging_Postbacks' ) ) {
	return;
}

class Omise_Chatbot_Facebook_Webhook_Event_Messaging_Postbacks {
	/**
	 * @var string  of an event name.
	 */
	public $event = 'messaging_postbacks';

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
		$payload = strtolower( 'payload_' . $messaging['postback']['payload'] );

		if ( method_exists( $this, $payload ) ) {
			$this->$payload( $messaging );
		}
	}

	/**
	 * @param  mixed $messaging
	 *
	 * @return void
	 *
	 * @since 3.2
	 */
	protected function payload_get_start_tapped( $messaging ) {
		$this->chatbot->message_to(
			$messaging['sender']['id'],
			array(
				'attachment' => array(
					'type'    => 'template',
					'payload' => array(
						'template_type' => 'button',
						'text'          => 'Hello, now you can talk to me :)',
						'buttons'       => array(
							array(
								'type'    => 'postback',
								'title'   => 'Check Items',
								'payload' => 'CHECKITEM'
							)
						)
					)
				)
			)
		);
	}
}
