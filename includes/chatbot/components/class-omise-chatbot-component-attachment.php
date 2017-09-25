<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Attachment' ) ) {
	return;
}

class Omise_Chatbot_Component_Attachment {
	/**
	 * @see https://developers.facebook.com/docs/messenger-platform/send-api-reference/contenttypes
	 *
	 * @var string  of the following: 'template', 'audio', 'image', 'video', 'file'
	 */
	protected $type;

	/**
	 * @var array
	 */
	protected $payload = array();

	/**
	 * @param array $attributes
	 */
	public function payload( $attributes ) {
		$this->payload = array_merge( $this->payload, $attributes );
	}

	/**
	 * @return array  of required attributes for create a button element on Facebook Messenger.
	 */
	public function to_array() {
		return array(
			'attachment' => array(
				'type'    => $this->type,
				'payload' => $this->payload
			)
		);
	}
}
