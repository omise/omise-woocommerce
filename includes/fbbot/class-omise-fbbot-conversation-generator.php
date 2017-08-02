<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Conversation_Generator' ) ) {
  return;
}

class Omise_FBBot_Conversation_Generator {
	private function __construct() {
		// Hide the constructor
	}

	public static function greeting_message( $sender_id ) {
		$greeting_message = Omise_FBBot_Message_Store::get_greeting_message( $sender_id  );
		$buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();
		
		return FB_Button_Template::create( $greeting_message, $buttons );
	}

	public static function helping_message() {
		$helping_message = Omise_FBBot_Message_Store::get_helping_message();
		$buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();

		return FB_Button_Template::create( $helping_message, $buttons );
	}

    public static function rechecking_order_number_message() {
        $rechecking_order_massage = Omise_FBBot_Message_Store::get_rechecking_order_number_message();
        return FB_Message_Item::create( $rechecking_order_massage );
    }

    public static function get_ordet_status_message( $order_id ) {
        $order_status = Omise_FBBot_WooCommerce::check_order_status( $order_id );

        if ( ! $order_status ) {
            return FB_Message_Item::create( Omise_FBBot_Message_Store::get_order_not_found_message() );
        }

        return FB_Message_Item::create( Omise_FBBot_Message_Store::get_order_has_found_message( $order_status ) );
    }

	public static function feature_products_message( $sender_id ) {
		$feature_products = Omise_FBBot_WCProduct::featured();

		if ( ! $feature_products ) {
            $product_is_empty = Omise_FBBot_Message_Store::get_feature_products_is_empty_message();
 
            return FB_Message_Item::create( $product_is_empty );
        }

      	$elements = array();

        foreach ( $feature_products as $product ) {
            $view_gallery_button = FB_Postback_Button_Item::create( __( 'Gallery ', 'omise' ) . $product->name, 'VIEW_PRODUCT__'.$product->id );

            $view_detail_button = FB_URL_Button_Item::create( __( 'View on website', 'omise' ), $product->permalink );

            $buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $sender_id .'&product_id=' . $product->id;
            $buy_now_button = FB_URL_Button_Item::create( __( 'Buy Now : ', 'omise' ) . $product->price .' '. $product->currency, $buying_url );

            $buttons = array( $view_gallery_button, $view_detail_button, $buy_now_button );
            $element = FB_Element_Item::create( $product->name, $product->short_description, $product->thumbnail_img, null, $buttons );

            array_push( $elements, $element );
        }

        return FB_Generic_Template::create( $elements );
	}

	public static function product_category_message() {
        $categories = Omise_FBBot_WCCategory::collection();

        $func = function( $category ) {
            $viewProductsButton = FB_Postback_Button_Item::create( __('View ') . $category->name, 'VIEW_CATEGORY_PRODUCTS__' . $category->slug );
          
            $buttons = array( $viewProductsButton );

            return FB_Element_Item::create( $category->name, $category->description, $category->thumbnail_img, NULL, $buttons );
        };

        $elements = array_map( $func, $categories );

        return FB_Generic_Template::create( $elements );
	}

	public static function product_list_in_category_message( $messenger_id, $category_slug ) {
        $products = Omise_FBBot_WCCategory::products( $category_slug );

        if ( ! $products ) {
            return FB_Message_Item::create( Omise_FBBot_Message_Store::get_products_is_empty_message() );
        }

        // Facebook list template is limit at 10
        $products = array_slice( $products, 0, 10 );
  	
        $elements = array();

        foreach ($products as $product) {
            $view_gallery_button = FB_Postback_Button_Item::create( __('Gallery ') . $product->name, 'VIEW_PRODUCT__'.$product->id );

            $view_detail_button = FB_URL_Button_Item::create( __( 'View on website', 'omise' ), $product->permalink );

            $buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $messenger_id .'&product_id=' . $product->id;
            $buy_now_button = FB_URL_Button_Item::create( __( 'Buy Now : ', 'omise' ) . $product->price.' '.$product->currency, $buying_url );

            $buttons = array( $view_gallery_button, $view_detail_button, $buy_now_button );
            $element = FB_Element_Item::create( $product->name, $product->short_description, $product->thumbnail_img, null, $buttons );

            array_push( $elements, $element );
        }

        return FB_Generic_Template::create( $elements );
	}

	public static function product_gallery_message( $messenger_id, $product_id ) {
        $product = Omise_FBBot_WCProduct::create( $product_id );

        if ( ! $product->attachment_images ) {
            return FB_Message_Item::create( Omise_FBBot_Message_Store::get_product_image_is_empty_message() );
        }

        $elements = array();

        foreach ( $product->attachment_images as $image_url ) {
            // For test on localhost
            // $image_url = str_replace('http://localhost:8888', 'your tunnel url', $image_url);

        	$buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $messenger_id .'&product_id=' . $product->id;
            $buy_now_button = FB_URL_Button_Item::create( __( 'Buy Now : ', 'omise' ) . $product->price.' '.$product->currency, $buying_url );

            $buttons = array( $buy_now_button );

            $element = FB_Element_Item::create( $product->name, $product->short_description, $image_url, null, $buttons );

            array_push( $elements, $element );
        }

        return FB_Generic_Template::create( $elements );
	}

	public static function prepare_confirm_order_message( $order_id ) {
        return FB_Message_Item::create( Omise_FBBot_Message_Store::get_prepare_confirm_order_message( $order_id ) );
	}

    public static function reply_for_purchase_message( $charge ) {
        $metadata = $charge->metadata;
        $order_id = $metadata->order_id;

        $message = '';

        switch ( $charge->status ) {
            case 'failed':
                $fail_message = $charge->failure_message;
                $message = Omise_FBBot_Message_Store::get_purchase_fail_message( $fail_message );
            break;

            case 'pending':
                if ( $charge->return_uri ) {
                    $message = Omise_FBBot_Message_Store::get_purchase_pending_with3ds_message();
                } else {
                    $message = Omise_FBBot_Message_Store::get_purchase_pending_message();
                }

            break;

            case 'reversed':
                $message = Omise_FBBot_Message_Store::get_purchase_reversed_message();
            break;

            case 'successful':
                $message = Omise_FBBot_Message_Store::get_purchase_completed_message();
            break;

            default:
                $message = Omise_FBBot_Message_Store::get_unknow_purchase_status_message();
            break;
        }

        return FB_Message_Item::create( $message );
    }

	public static function before_checking_order_message() { 
        return FB_Message_Item::create( Omise_FBBot_Message_Store::get_checking_order_helper_message() );
	}

	public static function unrecognized_message() {
        $unrecognized_message = Omise_FBBot_Message_Store::get_unrecognized_message();
        $buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();

		return FB_Button_Template::create( $unrecognized_message, $buttons );
	}
}