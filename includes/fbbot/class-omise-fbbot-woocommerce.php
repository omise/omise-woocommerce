<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if (  class_exists( 'Omise_FBBot_WooCommerce') ) {
  return;
}

class Omise_FBBot_WooCommerce {
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
    	return Omise_FBBot_WCProduct::create( $post->ID );
    };

    $products = array_map( $func, $loop->posts );
    wp_reset_postdata();
    return $products;
	}

	public static function get_product( $p_id ) {
		return Omise_FBBot_WCProduct::create( $p_id );
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
			return Omise_FBBot_WCCategory::create( $wc_category );
		};

		return array_map( $func, $product_categories );
	}

	public static function check_order_status( $order_id ) {
		// ORDER STATUS
    // pending, processing, on-hold, completed, cancelled, refunded, failed

    $order = wc_get_order( $order_id );

    if ( ! $order ) {
    	$message = FB_Message_Item::create( "Sorry, your order number not found. Can you try to check it again ? :'(" );
			return $message;
    }

    $status = $order->get_status();

    $message = FB_Message_Item::create( "BAMM! Your order status is '" . $order->get_status() . "' :]" );

    return $message;
	}

	public static function update_order_status( $order_id, $charge ) {
		$order = new WC_Order( $order_id );

		switch ( $charge->status ) {
			case 'failed':
				$order->update_status( 'failed' );
				break;
			
			case 'pending':
				$order->update_status( 'on-hold' );
				break;

			case 'reversed':
				$order->update_status( 'refunded' );
				break;

			case 'successful':
				$order->update_status( 'processing' );
				break;

			default:
				$order->update_status( 'on-hold' );
				break;
		}
		
		if ( $charge->paid ) {
			$order->update_status( 'completed' );
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

    $user = Omise_FBBot_User_Service::get_user( $messenger_id );
    
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