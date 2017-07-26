<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if (  class_exists( 'Omise_FBBot_WooCommerce') ) {
  return;
}

class Omise_FBBot_WooCommerce {
	public static function check_order_status( $order_id ) {
		// Order status in woocommerce
    // pending, processing, on-hold, completed, cancelled, refunded, failed

    $order = wc_get_order( $order_id );

    if (! $order ) {
    	return NULL;
    }

    $status = $order->get_status();

    return $status;
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