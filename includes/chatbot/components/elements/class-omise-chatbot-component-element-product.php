<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Element_Product' ) ) {
	return;
}

class Omise_Chatbot_Component_Element_Product extends Omise_Chatbot_Component_Element {
	/**
	 * @var WC_Product
	 */
	protected $product;

	/**
	 * @param WC_Product $product
	 */
	public function __construct( WC_Product $product ) {
		$this->product = $product;

		parent::__construct( $this->product->get_name() );

		$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $this->product->get_id() ) );

		$this->set_subtitle( $product->get_short_description() );
		$this->add_button(
			new Omise_Chatbot_Component_Button_Productgallery( $product )
		);
	}
}
