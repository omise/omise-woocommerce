<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Postback' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/buttons/postback
 */
class Omise_Chatbot_Component_Button_Postback extends Omise_Chatbot_Component_Button {
	protected $attributes = array(
		'type'    => 'postback',
		'title'   => '',
		'payload' => ''
	);
}
