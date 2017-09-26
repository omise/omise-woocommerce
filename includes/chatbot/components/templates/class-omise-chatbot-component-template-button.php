<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Template_Button' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/template/button
 */
class Omise_Chatbot_Component_Template_Button extends Omise_Chatbot_Component_Template {
	/**
	 * @var string
	 */
	protected $template_type = 'button';

	/**
	 * @var array
	 */
	protected $template_payload = array(
		'text'    => '',
		'buttons' => array()
	);

	/**
	 * @param string $text
	 */
	public function set_text( $text ) {
		$this->template_payload['text'] = $text;

		return $this;
	}

	/**
	 * @param  Omise_Chatbot_Component_Button $button
	 *
	 * @return self
	 */
	public function add_button( Omise_Chatbot_Component_Button $button ) {
		$this->template_payload['buttons'][] = $button->to_array();

		return $this;
	}

	/**
	 * @param  array $buttons  of Omise_Chatbot_Component_Button object
	 *
	 * @return self
	 */
	public function add_buttons( array $buttons ) {
		foreach ( $buttons as $button ) {
			$this->add_button( $button );
		}

		return $this;
	}
}
