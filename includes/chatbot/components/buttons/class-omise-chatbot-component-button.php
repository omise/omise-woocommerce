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
		'type' => ''
	);

	/**
	 * @param string $type
	 */
	public function __construct( $type ) {
		$this->attributes['type']  = $type;

		if ( method_exists( $this, 'default_attributes' ) ) {
			$this->set_attributes( $this->default_attributes() );
		}
	}

	/**
	 * @param array $attributes
	 */
	public function set_attributes( $attributes ) {
		$this->attributes = array_merge( $this->attributes, $attributes );
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param mixed  $default
	 */
	public function set_attribute( $key, $value, $default = null ) {
		$this->attributes[$key] = $value ? $value : $default;
	}

	/**
	 * @return array  of required attributes for create a button element on Facebook Messenger.
	 */
	public function to_array() {
		return $this->attributes;
	}
}
