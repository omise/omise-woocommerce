<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if( ! class_exists( 'Omise_Messenger_Bot_Conversation_Handler' ) ) {
	class Omise_Messenger_Bot_Conversation_Handler {
		 
		private function __construct() {
			// Hide the constructor
		}

		public static function handle_message_from( $sender_id, $user_message ) {
			$message = strtolower( $user_message );
      error_log( 'handle message : ' . $message );

      $greeting_words = array( 'hi', 'hello', 'yo' );
      $helping_words = array( 'help' );

      if ( in_array( $message, $greeting_words ) ) {
        $greeting_msg = Omise_Messenger_Bot_Conversation_Generator::greeting_message( $sender_id );

        $result = self::send_message_to( $sender_id, $greeting_msg );
        // check result again success/fail

      } else if ( in_array( $message, $helping_words ) ){
        $helping_msg = Omise_Messenger_Bot_Conversation_Generator::helping_message();
        $result = self::send_message_to( $sender_id, $helping_msg );
        // check result again success/fail

      } else if ( mb_substr( $message, 0, 1 ) == '#' ){
        // Checking order status from order number
        $checking_text = explode('#', $message);

        if ( ! $checking_text[1] ) {
          return;
        }

        $order_id = $checking_text[1];
        $order_status_message = Omise_Messenger_Bot_WooCommerce::check_order_status( $order_id );
        $result = self::send_message_to( $sender_id, $order_status_message );

      } else {
        $unrecognized_msg = Omise_Messenger_Bot_Conversation_Generator::unrecognized_message();

        $result = self::send_message_to( $sender_id, $unrecognized_msg );
        // check result again success/fail
      }

		}

    public static function handle_payload_from( $sender_id, $payload ) {
      error_log( 'handle payload : ' . $payload );
      switch ( $payload ) {
        case 'GET_START_CLICKED':
          $greeting_msg = Omise_Messenger_Bot_Conversation_Generator::greeting_message( $sender_id );

          $result = self::send_message_to( $sender_id, $greeting_msg );
          // check result again success/fail
          break;

        case 'PAYLOAD_FEATURE_PRODUCTS':
          $feature_products = Omise_Messenger_Bot_Conversation_Generator::feature_products_message( $sender_id );
          $result = self::send_message_to( $sender_id, $feature_products );
          break;

        case 'PAYLOAD_PRODUCT_CATEGORY':
          $product_category = Omise_Messenger_Bot_Conversation_Generator::product_category_message();
          $result = self::send_message_to( $sender_id, $product_category );
          break;

        case 'PAYLOAD_CHECK_ORDER':
          $welcome_to_check_order_message = Omise_Messenger_Bot_Conversation_Generator::before_checking_order_message();
          $result = self::send_message_to( $sender_id, $welcome_to_check_order_message );
          break;

        case 'PAYLOAD_HELP':
          $helping_msg = Omise_Messenger_Bot_Conversation_Generator::helping_message();
          $result = self::send_message_to( $sender_id, $helping_msg );
          break;
        
        default:
          # Custom payload :
          self::handle_custom_payload( $sender_id, $payload ); 
          break;
      }
    }

    private static function handle_custom_payload( $sender_id, $payload ) {
      error_log( 'handle_custom_payload : ' . $payload );

      $explode = explode('__', $payload);
      if ($explode[0] == 'VIEW_PRODUCT') {
        $product_id = $explode[1];
        $product_gallery_message = Omise_Messenger_Bot_Conversation_Generator::product_gallery_message( $sender_id, $product_id );

        $result = self::send_message_to( $sender_id, $product_gallery_message );

      } else if ($explode[0] == 'VIEW_CATEGORY_PRODUCTS') {
        $category_slug = $explode[1];
        $products_list_message = Omise_Messenger_Bot_Conversation_Generator::product_list_in_category_message( $sender_id, $category_slug );

        $result = self::send_message_to( $sender_id, $products_list_message );
      } else {
        error_log( 'handle default payload : ' . $payload );
      }
    }

    public static function handle_quick_reply_from( $sender_id, $quick_reply ) {
       error_log( 'handle quick_reply : ' . $quick_reply );
    } 

		public static function send_message_to( $receiver_id, $message ) {
			$message_api_url = 'https://graph.facebook.com/v2.6/me/messages?access_token=';
			$settings = Omise_Util::get_settings();
      if ( ! isset( $settings ) )
        return;

      $facebook_page_access_token = $settings['facebook_page_access_token'];
      if ( ! isset( $facebook_page_access_token ) )
      	return;

			$data = array(
        'timeout' => 60,
        'body' => array(
          'recipient' => array('id' => $receiver_id),
          'message' => $message
        )
      );

      $response = wp_safe_remote_post( $message_api_url . $facebook_page_access_token, $data );
      $body = json_decode( $response['body'] );

      if ( isset( $body->error ) ) {
        error_log( print_r( $body->error, true ) );
        return false;
      } 

      return true;
		}
	}

}