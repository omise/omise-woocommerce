<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Messenger_Bot_WCCategory' ) ) {
	class Omise_Messenger_Bot_WCCategory {
		static public function create( $wc_category ) {
			return new Omise_Messenger_Bot_WCCategory( $wc_category );
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
	}
}


if ( ! class_exists( 'Omise_Messenger_Bot_WCProduct' ) ) {
	class Omise_Messenger_Bot_WCProduct {
		static public function create( $product_id ) {
			return new Omise_Messenger_Bot_WCProduct( $product_id );
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
	}
}

if ( ! class_exists( 'Omise_Messenger_Bot_WooCommerce' ) ) {
	class Omise_Messenger_Bot_WooCommerce {

		public static function get_feature_products() {
			$featured_product_ids = wc_get_featured_product_ids();

			if ( ! $featured_product_ids ) {
				return NULL;
			}

			$func = function( $p_id ) {
				return Omise_Messenger_Bot_WCProduct::create( $p_id );
			};

			$products = array_map( $func, $featured_product_ids );

			return $products;
		}

		public static function get_products_by_category( $category_slug ) {
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
	    	return Omise_Messenger_Bot_WCProduct::create( $post->ID );
	    };

	    $products = array_map( $func, $loop->posts );
	    wp_reset_postdata();
	    return $products;
		}

		public static function get_product( $p_id ) {
			return Omise_Messenger_Bot_WCProduct::create( $p_id );
		}

		public static function get_product_categories() {
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
				return Omise_Messenger_Bot_WCCategory::create( $wc_category );
			};

			return array_map( $func, $product_categories );
		}

		public static function check_order_status( $order_id ) {
			// ORDER STATUS
	    // pending, processing, on-hold, completed, cancelled, refunded, failed

	    $order = wc_get_order( $order_id );

	    if ( ! $order ) {
	    	$message = FB_Message_Item::create( "Sorry, your order number not found. Can you try to check it again ? :'(" )->get_data();
				return $message;
	    }

	    $status = $order->get_status();

	    $message = FB_Message_Item::create( "BAMM! Your order status is '" . $order->get_status() . "' :]" )->get_data();

	    return $message;
		}

		public static function update_order_status( $order_id, $charge ) {
			$order = new WC_Order( $order_id );
			
			if ( $charge->paid ) {
				$order->update_status( 'completed' );
			} else {
				$order->update_status( 'pending' );
			}
		}

		public static function create_order( $product_id, $messenger_id ) {
			$order_data = array();
			$order_data['post_title' ] = 'Order from messenger bot : ' . $product_id;
			$order_data['post_name'] = 'Order for product id : ' . $product_id;
      $order_data['post_type'] = 'shop_order';
      $order_data['post_status'] = 'wc-' . apply_filters( 'woocommerce_default_order_status', 'pending' );

      $order_data['ping_status' ] = 'closed';
      $order_data['post_author' ] = 1;
      $order_data['post_content'] = 'Purchase via Messenger app : ' . $messenger_id;
      $order_data['comment_status'] = "open";
      
      $note = 'Purchase via Messenger app user : ' . $messenger_id;

      $user = Omise_Messenger_Bot_User_Service::get_user( $messenger_id );
      
      if ( ! $user ) 
      	return;

      $address = array(
      	'first_name' => $user->display_name
      	);

      $order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data ), true );

      $order = wc_get_order( $order_id );
      $order->add_product( get_product( $product_id ), 1 );
      $order->set_address( $address, 'billing' );
      $order->add_order_note( $note );
      $order->calculate_totals();

      return $order;

		}

	}
}