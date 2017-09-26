<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Productgallery' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Productgallery extends Omise_Chatbot_Component_Button_Postback {
	public function __construct() {
		parent::__construct( __( 'Gallery', 'omise' ) );

		$this->set_payload( Omise_Chatbot_Payloads::ACTION_PRODUCT_GALLERY );
	}
}
