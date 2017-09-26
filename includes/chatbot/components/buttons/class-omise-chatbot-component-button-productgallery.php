<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Productgallery' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Productgallery extends Omise_Chatbot_Component_Button_Postback {
	/**
	 * @param WC_Product $product
	 */
	public function __construct( WC_Product $product ) {
		parent::__construct( __( 'Gallery', 'omise' ) );

		$this->init_payload( $product );
	}

	/**
	 * @param WC_Product $product
	 */
	protected function init_payload( WC_Product $product ) {
		$payload = Omise_Chatbot_Payloads::create( Omise_Chatbot_Payloads::ACTION_PRODUCT_GALLERY );
		$payload->set_data(
			array( 'product_id' => $product->get_id() )
		);

		$this->set_payload( $payload->to_json() );
	}
}
