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
      $message = FB_Message_Item::create( __( "Sorry, your order number not found. Can you try to check it again ? :'(", 'omise' ) );
      return $message;
    }

    $message = FB_Message_Item::create( sprintf( __( "BAMM! Your order status is '%s' :]", 'omise' ), $order_status ) );

    return $message;
  }

	public static function feature_products_message( $sender_id ) {
		$feature_products = Omise_FBBot_WCProduct::featured();

		if ( ! $feature_products ) {
			$product_is_empty = Omise_FBBot_Message_Store::get_feature_products_is_empty_message();
    	$message = FB_Message_Item::create( $product_is_empty );

    	return $message;
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

    $feature_products_message = FB_Generic_Template::create( $elements );
    return $feature_products_message;
	}

	public static function product_category_message() {
		$categories = Omise_FBBot_WCCategory::collection();

    $func = function( $category ) {
      $viewProductsButton = FB_Postback_Button_Item::create( __('View ') . $category->name, 'VIEW_CATEGORY_PRODUCTS__' . $category->slug );
      
      $buttons = array( $viewProductsButton );
      $element = FB_Element_Item::create( $category->name, $category->description, $category->thumbnail_img, NULL, $buttons );

      return $element;
    };

    $elements = array_map( $func, $categories );

    $category_message = FB_Generic_Template::create( $elements );

    return $category_message;
	}

	public static function product_list_in_category_message( $messenger_id, $category_slug ) {
		$products = Omise_FBBot_WCCategory::products( $category_slug );

    if ( ! $products ) {
      $message = FB_Message_Item::create( __( "🤖  We don't have product on this category. We will do it soon <3", 'omise' ) );

      return $message;
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

    $message = FB_Generic_Template::create( $elements );
    return $message;
	}

	public static function product_gallery_message( $messenger_id, $product_id ) {
    $product = Omise_FBBot_WCProduct::create( $product_id );

    if ( ! $product->attachment_images ) {
      $message = FB_Message_Item::create( __( "🤖  Don't have image gallery on this product. We will do it soon <3", 'omise' ) );

      return $message;
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

    $message = FB_Generic_Template::create( $elements );
    return $message;
	}

	public static function prepare_confirm_order_message( $order_id ) {
    $message = FB_Message_Item::create( sprintf( __( '🤖 We received your order. Your OrderID is 👉 #%s 👈. We will process your order right away and send you a confirmation once it is complete ❤', 'omise' ), $order_id ) );

		return $message;
	}

  public static function reply_for_purchase_message( $charge ) {
    $metadata = $charge->metadata;
    $order_id = $metadata->order_id;

    $message = '';

    switch ( $charge->status ) {
        case 'failed':
        $fail_message = $charge->failure_message;
          $message = sprintf( __( 'Oops seems we cannot process your payment properly.. The reason is %s', 'omise' ), $fail_message );
          break;

        case 'pending':
          if ( $charge->return_uri ) {
            $message = __( 'However, due to a 3rd-party payment processor, this process might takes a little while.', 'omise' );
          } else {
            $message = __( "Now, the payment has been processing. I'll let you know once it done, thanks for your order.", 'omise' );
          }

          break;

        case 'reversed':
          $message = __( 'I just reverse your payment as your request, this process might take few days to return your balance due to the bank issuer you are using.', 'omise' );
          break;

        case 'successful':
          $message = __( 'Any process to do more? No worry my friend, all done now. Next we will verify your order and payment then ship it!', 'omise' );
          break;

        default:
          $message = __( 'BOOOOOOOOOOOOOOOOOOOO', 'omise' );
          break;
      }

      return FB_Message_Item::create( $message );
  }

	public static function before_checking_order_message() {
		$message = FB_Message_Item::create( __( ':) Sure!. You can put your order number follow ex. #12345', 'omise' ) );
		return $message;
	}

	public static function unrecognized_message() {
		$unrecognized_message = Omise_FBBot_Message_Store::get_unrecognized_message();
    $buttons = Omise_FBBot_Message_Store::get_default_menu_buttons();

		return FB_Button_Template::create( $unrecognized_message, $buttons );
	}
}