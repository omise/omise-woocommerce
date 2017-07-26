<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if (  class_exists( 'Omise_FBBot_WCProduct') ) {
  return;
}

class Omise_FBBot_WCProduct {
	static public function create( $product_id ) {
		return new Omise_FBBot_WCProduct( $product_id );
	} 

	public function __construct( $product_id ) {
		$product = new WC_Product( $product_id );

		$this->id = $product_id;
		$this->name = $product->get_name();
		$this->currency = get_woocommerce_currency();
		$this->permalink = get_permalink( $product_id );
		$this->description = $product->get_description();
		$this->short_description = $product->get_short_description();
		$this->attachment_ids = $product->get_gallery_image_ids();

		$this->thumbnail_img = $this->get_thumbnail_image( $product_id );
		$this->attachment_images = $this->get_attachment_images( $product->get_gallery_image_ids() );

		$regular_price = $product->get_regular_price();
		$sale_price = $product->get_sale_price();

		$this->price = ( ( $sale_price > 0 ) && ( $sale_price < $regular_price ) ) ? $sale_price : $regular_price;
	}

	protected function get_thumbnail_image( $product_id ) {
		$thumbnail_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ) );


		if ( ! $thumbnail_image[0] ) {
			return NULL;
		}

		return $thumbnail_image[0];
	}

	protected function get_attachment_images( $attachment_ids ) {
		$func = function( $attachment_id ) {
			$image_link = wp_get_attachment_url( $attachment_id );
			return $image_link;
		};

		$attachment_images = array_map( $func, $attachment_ids );
		return $attachment_images;
	}

	public static function featured() {
		$featured_product_ids = wc_get_featured_product_ids();

		if ( ! $featured_product_ids ) {
			return NULL;
		}

		$func = function( $p_id ) {
			return self::create( $p_id );
		};

		$products = array_map( $func, $featured_product_ids );

		return $products;
	}
}