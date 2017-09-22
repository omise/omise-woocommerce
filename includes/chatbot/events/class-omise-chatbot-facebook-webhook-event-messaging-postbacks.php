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
		// Event payload.
		$sender_id = $messaging['sender']['id'];
		$payload   = $messaging['postback']['payload'];

		// TODO: This is just to prove concept, need to find other solution.
		wp_safe_remote_post(
			$this->chatbot->get_facebook_message_endpoint(),
			array(
				'timeout' => 60,
				'body'    => array(
					'recipient' => array('id' => $sender_id),
					'message'   => array(
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
				)
			)
		);
	}
}
