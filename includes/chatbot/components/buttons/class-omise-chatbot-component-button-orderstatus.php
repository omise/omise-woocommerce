<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Orderstatus' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Orderstatus extends Omise_Chatbot_Component_Button_Postback {
	public function __construct() {
		$this->attributes['title']   = __( 'Check order status', 'omise' );
		$this->attributes['payload'] = Omise_Chatbot_Payloads::ACTION_ORDER_STATUS;
	}
}
