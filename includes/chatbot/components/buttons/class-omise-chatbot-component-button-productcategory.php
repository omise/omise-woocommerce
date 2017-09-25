<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Productcategory' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Productcategory extends Omise_Chatbot_Component_Button_Postback {
	/**
	 * @param string $title
	 * @param string $payload
	 */
	public function __construct() {
		$this->attributes['title']   = __( 'Product category', 'omise' );
		$this->attributes['payload'] = Omise_Chatbot_Payloads::ACTION_PRODUCT_CATEGORY;
	}
}
