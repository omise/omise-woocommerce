<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Featuredproducts' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Featuredproducts extends Omise_Chatbot_Component_Button_Postback {
	public function __construct() {
		$this->attributes['title']   = __( 'Featured products', 'omise' );
		$this->attributes['payload'] = Omise_Chatbot_Payloads::ACTION_FEATURED_PRODUCTS;
	}
}
