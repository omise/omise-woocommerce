<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Element_Productcategory' ) ) {
	return;
}

class Omise_Chatbot_Component_Element_Productcategory extends Omise_Chatbot_Component_Element {
	public function __construct( $category ) {
		parent::__construct( $category->name );

		$description  = ! empty( $category->description ) ? $category->description : 'N/A';
		$this->set_subtitle( $description );

		$thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$this->set_image_url( wp_get_attachment_url( $thumbnail_id ) );
		}

		$this->add_button( new Omise_Chatbot_Component_Button_Productlist( $category ) );
	}
}
