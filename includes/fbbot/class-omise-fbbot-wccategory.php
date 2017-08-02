<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if (  class_exists( 'Omise_FBBot_WCCategory') ) {
	return;
}

class Omise_FBBot_WCCategory {
	static public function create( $wc_category ) {
		return new Omise_FBBot_WCCategory( $wc_category );
	}

	public function __construct( $wc_category ) {
		$this->id = $wc_category->term_id;
		$this->name = $wc_category->name;
		$this->description = $wc_category->description;
		$this->slug = $wc_category->slug;
		$this->permalink = get_term_link( $wc_category->slug, 'product_cat' );

		$thumbnail_id = get_woocommerce_term_meta( $wc_category->term_id, 'thumbnail_id', true );
		$this->thumbnail_img = wp_get_attachment_url( $thumbnail_id );
	}

	public static function products( $category_slug ) {
		$args = array(
			'posts_per_page' => -1,
			'tax_query' => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_cat',
					'field' => 'slug',
					'terms' => $category_slug
				)
			),
			'post_type' => 'product',
			'orderby' => 'title,'
		);

		$loop = new WP_Query( $args );

		if ( ! $loop->have_posts() ) {
			return NULL;
		}

		$func = function ($post) {
			return Omise_FBBot_WCProduct::create( $post->ID );
		};

		$products = array_map( $func, $loop->posts );
		wp_reset_postdata();

		return $products;
	}

	public static function collection() {
		$args = array(
			'taxonomy' => 'product_cat',
			'orderby'    => 'name',
			'order'      => 'ASC',
			'pad_counts' => true,
			'child_of'   => '',
			'hide_empty' => false
		);

		$product_categories = get_terms( $args );
		
		$func = function( $wc_category ) {
			return self::create( $wc_category );
		};

		return array_map( $func, $product_categories );
	}
}