<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Productpage' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Productpage extends Omise_Chatbot_Component_Button_Url {
	/**
	 * @param WC_Product $product
	 */
	public function __construct( WC_Product $product ) {
		parent::__construct( __( 'View on website', 'omise' ) );

		$this->set_url( get_permalink( $product->get_id() ) );
	}
}
