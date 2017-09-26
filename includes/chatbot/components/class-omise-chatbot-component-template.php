<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Template' ) ) {
	return;
}

class Omise_Chatbot_Component_Template extends Omise_Chatbot_Component_Attachment {
	/**
	 * @var string
	 */
	protected $type = 'template';

	/**
	 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/templates
	 *
	 * @var string  of the following: 'generic', 'button'
	 */
	protected $template_type;

	/**
	 * @var array
	 */
	protected $template_payload = array();

	/**
	 * @return array  of required attributes for create an element on Facebook Messenger.
	 */
	public function to_array() {
		$this->payload(
			array_merge(
				array( 'template_type' => $this->template_type ),
				$this->template_payload
			)
		);

		return parent::to_array();
	}
}
