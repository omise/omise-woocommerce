<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Message_Store' ) ) {
	return;
}

class Omise_FBBot_Message_Store {
	private function __construct() {
		// Hide the constructor
	}

	public static function get_greeting_message( $sender_id  ) {
		$user = Omise_FBBot_User_Service::get_user( $sender_id );
		$shop_name = get_bloginfo( 'name' );

		$greeting_1 = sprintf(
			__( 'Hello, %1$s 👋 Welcome to %2$s. What are you looking for? 🤔', 'omise' ),
			$user['first_name'],
			$shop_name
		);

		$greeting_2 = sprintf(
			__( 'Hello, welcome to %1$s 👋 What can I do for you today? 😃', 'omise' ),
			$shop_name
		);

		$greeting_3 = __( 'Hi there! 👋 Feel like shopping today? 🛍 Let me show you around!', 'omise' );

		$greeting_4 = sprintf(
			__( 'Hello %1$s 👋 How can I help?', 'omise' ),
			$user['first_name']
		);

		$greeting_5 = sprintf(
			__( 'Thank you for visiting %1$s. How can I help you  %2$s?', 'omise' ),
			$shop_name,
			$user['first_name']
		);

		$greeting_6 = sprintf(
			__( "I'm glad you visited %1$s. How may I assist ?", 'omise' ),
			$shop_name
		);

		$greeting_7 = sprintf(
			__( 'Welcome to %1$s %2$s! How may I assist?', 'omise' ),
			$shop_name,
			$user['first_name']
		);

		$greeting_messages = array( $greeting_1, $greeting_2, $greeting_3, $greeting_4, $greeting_5, $greeting_6, $greeting_7 );

		return self::ramdomArrayOfMessage( $greeting_messages );
	}

	public static function get_helping_message() {
		$helping_1 = __( "🤖 I can help you with lots of things. Start by selecting from one of the options below 👇", 'omise' );

		$helping_2 = __( "Let's get started! Choose from one of the options below 🤖", 'omise' );

		$helping_3 = __( "Let me guide you through.. first, choose from an option below 👇", 'omise' );

		$helping_4 = __( "Let me help you. 🛍 Choose from an option below 👇", 'omise' );

		$helping_5 = __( "Here's what I can help you with 👇", 'omise' );

		$helping_6 = __( "You came to the right place for help. Here's what I can do for you 👇", 'omise' );

		$helping_messages = array( $helping_1, $helping_2, $helping_3, $helping_4, $helping_5, $helping_6 );

		return self::ramdomArrayOfMessage( $helping_messages );
	}

	public static function get_unrecognized_message() {
		$unrecognized_1 = __( "🤕 I wish I could understand you, maybe one day! For now, here's what I can help you with ", 'omise' );

		$unrecognized_2 = __( "🤖 Uh oh, not sure what you mean. But here's how I can help.", 'omise' );

		$unrecognized_3 = __( "🤖 I'm not sure what you mean, but here's how I can help.", 'omise' );

		$unrecognized_4 = __( "🤖 Not sure what you mean. Here's what I can help you with.", 'omise' );

		$unrecognized_5 = __( "🤖 What do you mean? Here's the things I can help you with 👇", 'omise' );

		$unrecognized_messages = array( $unrecognized_1, $unrecognized_2, $unrecognized_3, $unrecognized_4, $unrecognized_5 );

		return self::ramdomArrayOfMessage( $unrecognized_messages );
	}

	public static function get_feature_products_is_empty_message() {
		$empty_1 = __( "🤖 Sorry, there're no featured products available. We hope to stock up soon. ❤️", 'omise' );

		$empty_2 = __( "🤖 Featured products are not yet available. Can I show you something else?", 'omise' );

		$empty_3 = __( "🤖 I wish there's something I could show you. But right now, there are no featured products available.", 'omise' );

		$empty_product_messages = array( $empty_1, $empty_2, $empty_3 );

		return self::ramdomArrayOfMessage( $empty_product_messages );
	}

	public static function get_products_is_empty_message() {
		$empty_1 = __( "🤖 Category is empty. We'll be adding more products soon. ❤️", 'omise');

		$empty_2 = __( "🤖 We need to re-stock. Can I show you something else?", 'omise');

		$empty_3 = __( "🤖 The category is empty. We'll load up again soon. Can I show you something else?", 'omise');

		$empty_4 = __( "🤖 I'm sorry but featured products are not available. Can I show you something else?", 'omise');

		$empty_product_messages = array( $empty_1, $empty_2, $empty_3, $empty_4 );

		return self::ramdomArrayOfMessage( $empty_product_messages );
	}

	public static function get_product_image_is_empty_message() {
		$empty_1 = __( "🤖 Image gallery not ready yet, but we're getting there. Stay tuned! ❤️", 'omise');

		$empty_2 = __( "🤖 I know you shop easier with pics. We'll try to upload pictures before your next visit ❤️", 'omise');

		$empty_3 = __( "🤖 We're uploading pictures real soon. Sorry for the inconvenience ☹️", 'omise');

		$empty_4 = __( "🤖 No images available yet.. I will definately let the store owner know.", 'omise');

		$empty_image_messages = array( $empty_1, $empty_2, $empty_3, $empty_4 );

		return self::ramdomArrayOfMessage( $empty_image_messages );
	}

	public static function get_checking_order_helper_message() {
		$checking_1 = __( "😄 What's your order number? Just let me know like this, #12345", 'omise' );

		$checking_2 = __( "😄 Please give me your order number. It's the one that looks like this #12345 👍", 'omise' );

		$checking_3 = __( "😄 Type in your order number. It's something like this, #12345. 👍", 'omise' );

		$checking_4 = __( "😄 I can check your order number. Please send it over like this, #12345", 'omise' );

		$checking_order_messages = array( $checking_1, $checking_2, $checking_3, $checking_4 );

		return self::ramdomArrayOfMessage( $checking_order_messages );
	}

	public static function get_rechecking_order_number_message() {
		$rechecking_1 = __( "🙇 To check your order status, please enter your order number (ex. #12345) 👍", 'omise' );

		$rechecking_2 = __( "🙇 Type down your order number (example: #12345) 👍", 'omise' );

		$rechecking_3 = __( "🙇 What's your order number? Let me know. I'll check it out. (example #12345) 👍", 'omise' );

		$rechecking_order_messages = array( $rechecking_1, $rechecking_2, $rechecking_3 );

		return self::ramdomArrayOfMessage( $rechecking_order_messages );
	}

	public static function get_order_not_found_message() {
		$not_found_1 = __( "🙇 Are you sure you got the order number right? We can't seem to find it. Can you try again?", 'omise' );

		$not_found_2 = __( "🙇 Can you check that you got the order number right? We can't find your order :(", 'omise' );
		
		$not_found_3 = __( "🙇 Hmmm, that's weird.. We can't find your order. Sure you got the order number right?", 'omise' );
		
		$not_found_messages = array( $not_found_1, $not_found_2, $not_found_3 );

		return self::ramdomArrayOfMessage( $not_found_messages );
	}

	public static function get_order_has_found_message( $order_status ) {
		$status_1 = sprintf(
			__( 'Your order status is %s.', 'omise' ),
			$order_status
		);

		$status_2 = sprintf(
			__( 'Here you go! 💥 Your order status is %s.', 'omise' ),
			$order_status
		);

		$status_messages = array( $status_1, $status_2 );

		return self::ramdomArrayOfMessage( $status_messages );
	}

	public static function get_prepare_confirm_order_message( $order_id ) {
		return sprintf( __( '🤖  We received your order. Your OrderID is 👉 #%s 👈. We will process your order right away and send you a confirmation once it is complete ❤', 'omise' ), $order_id );
	}

	public static function get_purchase_fail_message( $fail_message ) {
		$fail_1 = sprintf( __( '😧 Payment cannot be processed due to %s', 'omise' ), $fail_message );

		$fail_2 = sprintf( __( '😧 We are unable to process your payment at this moment due to %s', 'omise' ), $fail_message );

		$fail_3 = sprintf( __( "😧 We're sorry. Your payment cannot be processed at this moment due to %s", 'omise' ), $fail_message );

		$fail_4 = sprintf( __( '😞 We are unable to process your payments due to %s. We apologize for the inconvenience.', 'omise' ), $fail_message );

		$fail_messages = array( $fail_1, $fail_2, $fail_3, $fail_4 );

		return self::ramdomArrayOfMessage( $fail_messages );
	}

	public static function get_purchase_pending_with3ds_message() {
		return __( 'However, due to a 3rd-party payment processor, this process might takes a little while.', 'omise' );
	}

	public static function get_purchase_pending_message() {
		$pending_1 = __( "😇 Hold on a minute. Payment is being processed. I'll let you know once it's done.", 'omise' );

		$pending_2 = __( "😇 Keep calm. Your payment is being processed.", 'omise' );

		$pending_messages = array( $pending_1, $pending_2 );

		return self::ramdomArrayOfMessage( $pending_messages );
	}

	public static function get_purchase_reversed_message() {
		$reversed_1 = __( "👍 Your payment has been reversed. We're currently working with banks to return the amount to your account.", 'omise' );

		$reversed_2 = __( "👍 Your payment has been reversed. The balance will be returned to you shortly.", 'omise' );

		$reversed_messages = array( $reversed_1, $reversed_2 );

		return self::ramdomArrayOfMessage( $reversed_messages );
	}

	public static function get_purchase_completed_message() {
		$completed_1 = __( "👍 Boom Done. Your order and payment will be verified once again, before we ship your products.", 'omise' );

		$completed_2 = __( "👍 Thank you for shopping with us. We will verify your order and payment, and will proceed to shipping your items.", 'omise' );

		$completed_3 = __( "👍 Thanks for shopping with us. Your order and payment will be verified, and we'll be shipping shortly. ", 'omise' );

		$completed_messages = array( $completed_1, $completed_2, $completed_3 );

		return self::ramdomArrayOfMessage( $completed_messages );
	}

	public static function get_unknow_purchase_status_message() {
		return __( 'BOOOOOOOOOOOOOOOOOOOO', 'omise' );
	}

	public static function get_default_menu_buttons() {
		$payload = Omise_FBBot_Payload;

		$feature_products_button = FB_Postback_Button_Item::create( __( 'Featured products', 'omise' ), $payload::FEATURE_PRODUCTS );
		$category_button = FB_Postback_Button_Item::create( __( 'Product category', 'omise' ), $payload::PRODUCT_CATEGORY );
		$check_order_button = FB_Postback_Button_Item::create( __( 'Check order status', 'omise' ), $payload::CHECK_ORDER );
		$call_shop_owner_button = FB_Postback_Button_Item::create( __( 'Call shop owner', 'omise' ), $payload::CALL_SHOP_OWNER );

		return array( $feature_products_button, $category_button , $call_shop_owner_button );
	}

	public static function check_greeting_words( $message ) {
		$greeting_words = array( 'hi', 'hello' );
		return in_array( $message, $greeting_words );
	}

	public static function check_helping_words( $message ) {
		$helping_words = array( 'help' );
		return in_array( $message, $helping_words );
	}

	public static function check_order_checking( $message ) {
		return ( mb_substr( $message, 0, 1 ) == '#' );
	}

	private static function ramdomArrayOfMessage( $messages) {
		return $messages[ mt_rand( 0, count( $messages ) - 1 ) ];
	}

}