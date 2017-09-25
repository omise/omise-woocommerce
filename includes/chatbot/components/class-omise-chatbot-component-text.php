<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Text' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-api-reference/text-message
 */
class Omise_Chatbot_Component_Text {
	/**
	 * @var array
	 */
	protected $attributes = array(
		'text' => ''
	);

	/**
	 * @param string $text
	 */
	public function set_text( $text ) {
		$this->attributes['text'] = $text;
	}

	/**
	 * @return array  of required attributes for create an element on Facebook Messenger.
	 */
	public function to_array() {
		return $this->attributes;
	}
}
