<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/buttons
 */
class Omise_Chatbot_Component_Button {
	/**
	 * @var array
	 */
	protected $attributes = array(
		'type'  => '',
		'title' => ''
	);

	/**
	 * @return array  of required attributes for create a button element on Facebook Messenger.
	 */
	public function to_array() {
		return $this->attributes;
	}
}
