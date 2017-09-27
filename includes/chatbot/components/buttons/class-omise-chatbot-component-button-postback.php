<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Postback' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/buttons/postback
 */
class Omise_Chatbot_Component_Button_Postback extends Omise_Chatbot_Component_Button {
	/**
	 * @param string $title
	 */
	public function __construct( $title ) {
		parent::__construct( 'postback' );

		$this->set_title( $title );
	}

	/**
	 * @return array
	 */
	public function default_attributes() {
		return array(
			'title'   => '',
			'payload' => ''
		);
	}

	/**
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->set_attribute( 'title', $title );
	}

	/**
	 * @param string $payload
	 */
	public function set_payload( $payload ) {
		$this->set_attribute( 'payload', $payload );
	}
}
