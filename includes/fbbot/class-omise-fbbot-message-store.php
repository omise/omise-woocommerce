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

		$greeting_message_1 = __( ':D Hello ' ) . $user->display_name . ' ';
		$greeting_message_2 = __( 'Welcome to ') . $shop_name;
		$greeting_message_3 = __( ', what are you looking for today ?' );

		$greeting_message = $greeting_message_1 . $greeting_message_2 . $greeting_message_3;

		return $greeting_message;
	}

	public static function get_helping_message() {
		$helping_message_1 = __( "😚 Don't worry, in spite of the fact i'm just a bot but i can help you. You can choose 1 choice from below." );

		$helping_message_2 = __( "Sure let me help you to shopping. You can choose 1 menu from below 😉" );

		$helping_messages = array( $helping_message_1, $helping_message_2 );

		$helping_message = self::ramdomArrayOfMessage( $helping_messages );

		return $helping_message;
	}

	public static function get_unrecognized_message() {
		$default_message_1 = __( ":'(  I wish I could understand you, maybe one day! I’m here to help you shopping on Messenger app, Do you want to buy something ?" );

		$default_message_2 = __( '🤖  Oh, I’m just a bot! but i have a cool stuff for cool people like you. Which do you like best ?' );

		$default_message_3 = __( '🤖  I’m so sorry, I don’t understand what you tell me, but i will let my shop owner know and told him to help you.' );

		$default_messages = array( $default_message_1, $default_message_2, $default_message_3 );

		$default_message = self::ramdomArrayOfMessage( $default_messages );

		return $default_message;
	}

	public static function get_feature_products_is_empty_message() {
		return __( "🤖  We don't have feature product for now. We will do it soon <3" );
	}

	public static function get_products_is_empty_message() {
		return __( "🤖  We don't have product on this category. We will do it soon <3" );
	}

	public static function get_product_image_is_empty_message() {
		return __( "🤖  This product don't have image gallery. We will do it soon <3" );
	}

	public static function get_prepare_confirm_order_message() {
		return __( "🤖  Received your order. We will process your order right away and send you a confirmation and order number once it is complete ❤️ " );
	}

	public static function get_thanks_for_purchase_message( $order_id ) {
		return __( "<3 Thank you for your purchase :). Your order number is #" ) . $order_id;
	}

	public static function get_checking_order_helper_message() {
		return __( ":) Sure!. You can put your order number follow ex. #12345" );
	}

	public static function get_default_menu_buttons() {
		$payload = Omise_FBBot_Payload;

		$feature_products_button = FB_Postback_Button_Item::create( __('Feature products'), $payload::FEATURE_PRODUCTS )->get_data();
		$category_button = FB_Postback_Button_Item::create( __('Product category'), $payload::PRODUCT_CATEGORY )->get_data();
		$check_order_button = FB_Postback_Button_Item::create( __('Check order status'), $payload::CHECK_ORDER )->get_data();

		$buttons = array( $feature_products_button, $category_button , $check_order_button);

		return $buttons;
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
		return $messages[ mt_rand( 0, count( $messages ) - ( count( $messages )-1 ) ) ];
	}

}