<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Element' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/template/generic
 */
class Omise_Chatbot_Component_Element {
	/**
	 * @var array
	 */
	protected $attributes = array(
		'title' => ''
	);

	/**
	 * @param string $title
	 */
	public function __construct( $title ) {
		$this->attributes['title'] = $title;
	}

	/**
	 * @param string $subtitle
	 */
	public function set_subtitle( $subtitle ) {
		$this->attributes['subtitle'] = $subtitle;
	}

	/**
	 * @param string $image_url
	 */
	public function set_image_url( $image_url ) {
		$this->attributes['image_url'] = $image_url;
	}

	/**
	 * @param string $default_action
	 */
	public function set_default_action( $default_action ) {
		$this->attributes['default_action'] = $default_action;
	}

	/**
	 * @param Omise_Chatbot_Component_Button $button
	 */
	public function add_button( Omise_Chatbot_Component_Button $button ) {
		$this->attributes['buttons'][] = $button->to_array();
	}

	/**
	 * @param array $buttons  of Omise_Chatbot_Component_Button object
	 */
	public function add_buttons( array $buttons ) {
		foreach ( $buttons as $button ) {
			$this->add_button( $button );
		}
	}

	/**
	 * @return array  of required attributes for create an element on Facebook Messenger.
	 */
	public function to_array() {
		return $this->attributes;
	}
}
