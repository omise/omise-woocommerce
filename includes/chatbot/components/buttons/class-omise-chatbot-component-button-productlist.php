<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Productlist' ) ) {
	return;
}

class Omise_Chatbot_Component_Button_Productlist extends Omise_Chatbot_Component_Button_Postback {
	/**
	 * @param WP_Term $category
	 */
	public function __construct( WP_Term $category ) {
		parent::__construct( __( 'View products', 'omise' ) );

		$this->init_payload( $category );
	}

	/**
	 * @param WP_Term $category
	 */
	protected function init_payload( WP_Term $category ) {
		$payload = Omise_Chatbot_Payloads::create( Omise_Chatbot_Payloads::ACTION_PRODUCT_LIST );
		$payload->set_data(
			array( 'category_slug' => $category->slug )
		);

		$this->set_payload( $payload->to_json() );
	}
}
