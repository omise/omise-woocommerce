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

	public static function products( $category_slug, $paged = 1 ) {
		// Facebook list template is limit at 10
		$args = array(
			'posts_per_page' => 10,
    		'paged' => $paged,
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

		$total_pages = $loop->max_num_pages;

		if ( ! $loop->have_posts() ) {
			return NULL;
		}

		$current_page = max(1, $paged);

		$func = function ($post) {
			return Omise_FBBot_WCProduct::create( $post->ID );
		};

		$products = array_map( $func, $loop->posts );
		wp_reset_postdata();

		$product_data = array(
			'products' => $products,
			'current_page' => $current_page,
			'total_pages' => $total_pages
		);

		return $product_data;
	}

	public static function collection( $paged = 1 ) {
		$args = array(
			'taxonomy' => 'product_cat',
			'orderby'    => 'id',
			'order'      => 'ASC',
			'pad_counts' => true,
			'child_of'   => '',
			'hide_empty' => false,
			'number' => 2,
            'offset' => $paged,
		);

		$product_categories = get_terms( $args );
		$total_pages = self::max_num_pages_collection();
		$current_page = max(1, $paged);

		$func = function( $wc_category ) {
			return self::create( $wc_category );
		};

		$categories = array_map( $func, $product_categories );

		$categories_data = array(
			'categories' => $categories,
			'current_page' => $current_page,
			'total_pages' => $total_pages
		);

		return $categories_data;
	}

	public static function max_num_pages_collection() {
		$args = array(
			'taxonomy' => 'product_cat',
			'pad_counts' => true,
			'child_of'   => '',
			'hide_empty' => false
		);

		$product_categories = get_terms( $args );
		$total_categories = count($product_categories);



		$total_pages = ceil( $total_categories / 2 );

		error_log('total_categories : ' . count($product_categories));
		error_log('total_pages : ' . $total_pages);

		return $total_pages;
	}
}