<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Productgallery' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Productgallery extends Omise_Chatbot_Component_Button_Postback {
	public function __construct() {
		$this->attributes['title']   = __( 'See gallery', 'omise' );
		$this->attributes['payload'] = 'ACTION_PRODUCT_GALLERY';
	}
}
