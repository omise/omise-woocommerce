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
		
		return FB_Button_Template::create( $greeting_message, $buttons )->get_data();
	}

	public static function helping_message() {
		$helping_message = Omise_FBBot_Message_Store::get_helping_message();
		$buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();

		return FB_Button_Template::create( $helping_message, $buttons )->get_data();
	}

  public static function rechecking_order_number_message() {
    $rechecking_order_massage = Omise_FBBot_Message_Store::get_rechecking_order_number_message();
    return FB_Message_Item::create( $rechecking_order_massage )->get_data();
  }

	public static function feature_products_message( $sender_id ) {
		$feature_products = Omise_FBBot_WooCommerce::get_feature_products();

		if ( ! $feature_products ) {
			$product_is_empty = Omise_FBBot_Message_Store::get_feature_products_is_empty_message();
    	$message = FB_Message_Item::create( $product_is_empty )->get_data();

    	return $message;
  	}

  	$elements = array();

    foreach ( $feature_products as $product ) {
    	$view_gallery_button = FB_Postback_Button_Item::create( __('Gallery ') . $product->name, 'VIEW_PRODUCT__'.$product->id )->get_data();

      $view_detail_button = FB_URL_Button_Item::create( __('View on website'), $product->permalink )->get_data();

      $buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $sender_id .'&product_id=' . $product->id;
      $buy_now_button = FB_URL_Button_Item::create( 'Buy Now : '.$product->price.' '.$product->currency, $buying_url )->get_data();

      $buttons = array( $view_gallery_button, $view_detail_button, $buy_now_button );
      $element = FB_Element_Item::create( $product->name, $product->short_description, $product->thumbnail_img, null, $buttons )->get_data();

      array_push( $elements, $element );
    }

    $feature_products_message = FB_Generic_Template::create( $elements )->get_data();
    return $feature_products_message;
	}

	public static function product_category_message() {
		$categories = Omise_FBBot_WooCommerce::get_product_categories();

    $func = function( $category ) {
      $viewProductsButton = FB_Postback_Button_Item::create( __('View ') . $category->name, 'VIEW_CATEGORY_PRODUCTS__' . $category->slug )->get_data();
      
      $buttons = array( $viewProductsButton );
      $element = FB_Element_Item::create( $category->name, $category->description, $category->thumbnail_img, NULL, $buttons )->get_data();

      return $element;
    };

    $elements = array_map( $func, $categories );

    $category_message = FB_Generic_Template::create( $elements )->get_data();

    return $category_message;
	}

	public static function product_list_in_category_message( $messenger_id, $category_slug ) {
		$products = Omise_FBBot_WooCommerce::get_products_by_category( $category_slug );

    if ( ! $products ) {
      $message = FB_Message_Item::create( __("🤖  We don't have product on this category. We will do it soon <3") )->get_data();

      return $message;
    }

    // Facebook list template is limit at 10
    $products = array_slice( $products, 0, 10 );
  	
    $elements = array();

    foreach ($products as $product) {
    	$view_gallery_button = FB_Postback_Button_Item::create( __('Gallery ') . $product->name, 'VIEW_PRODUCT__'.$product->id )->get_data();

      $view_detail_button = FB_URL_Button_Item::create( __('View on website'), $product->permalink )->get_data();

      $buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $messenger_id .'&product_id=' . $product->id;
      $buy_now_button = FB_URL_Button_Item::create( __('Buy Now : ') . $product->price.' '.$product->currency, $buying_url )->get_data();

      $buttons = array( $view_gallery_button, $view_detail_button, $buy_now_button );
      $element = FB_Element_Item::create( $product->name, $product->short_description, $product->thumbnail_img, null, $buttons )->get_data();

      array_push( $elements, $element );
    }

    $message = FB_Generic_Template::create( $elements )->get_data();
    return $message;
	}

	public static function product_gallery_message( $messenger_id, $product_id ) {
    $product = Omise_FBBot_WCProduct::create( $product_id );

    if ( ! $product->attachment_images ) {
      $message = FB_Message_Item::create( __("🤖  Don't have image gallery on this product. We will do it soon <3") )->get_data();

      return $message;
    }

    $elements = array();

    foreach ( $product->attachment_images as $image_url ) {
      // For test on localhost
      // $image_url = str_replace('http://localhost:8888', 'your tunnel url', $image_url);

    	$buying_url = site_url() . '/pay-on-messenger/?messenger_id=' . $messenger_id .'&product_id=' . $product->id;
      $buy_now_button = FB_URL_Button_Item::create( __('Buy Now : ') . $product->price.' '.$product->currency, $buying_url )->get_data();

      $buttons = array( $buy_now_button );

      $element = FB_Element_Item::create( $product->name, $product->short_description, $image_url, null, $buttons )->get_data();

      array_push( $elements, $element );
    }

    $message = FB_Generic_Template::create( $elements )->get_data();
    return $message;
	}

	public static function prepare_confirm_order_message() {
		$message = FB_Message_Item::create( __('🤖  Received your order. We will process your order right away and send you a confirmation and order number once it is complete ❤') )->get_data();
		return $message;
	}

	public static function thanks_for_purchase_message( $order_id ) {
		$message = FB_Message_Item::create( __( '<3 Thank you for your purchase :). Your order number is #' ) . $order_id )->get_data();
		return $message;
	}

	public static function before_checking_order_message() {
		$message = FB_Message_Item::create( __( ':) Sure!. You can put your order number follow ex. #12345' ) )->get_data();
		return $message;
	}

	public static function unrecognized_message() {
		$unrecognized_message = Omise_FBBot_Message_Store::get_unrecognized_message();
    $buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();

		return FB_Button_Template::create( $unrecognized_message, $buttons )->get_data();
	}
}