<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Request_Handler' ) ) {
  return;
}

class Omise_FBBot_Request_Handler {
	
		private function __construct() {
			// Hide the constructor
		}

		public static function handle_message_from( $sender_id, $user_message ) {
			$message_store = Omise_Messenger_Bot_Message_Store;
			$message = strtolower( $user_message );
      error_log( 'handle message : ' . $message );

      if ( $message_store::check_greeting_words( $message ) ) {

      } else if ( $message_store::check_helping_words( $message ) ){

      } else if ( $message_store::check_order_checking( $message ) ){
        // Checking order status from order number
        $checking_text = explode('#', $message);

        if ( ! $checking_text[1] ) {
          return;
        }

        $order_id = $checking_text[1];

      } else {
      	// Handle unrecognize message
      }
    }

    public static function handle_payload_from( $sender_id, $payload ) {
      error_log( 'handle payload : ' . $payload );
      switch ( $payload ) {
      	case Omise_FBBot_Payload::GET_START_CLICKED:
      		break;

      	case Omise_FBBot_Payload::FEATURE_PRODUCTS:
      		break;

      	case Omise_FBBot_Payload::PRODUCT_CATEGORY:
      		break;

      	case Omise_FBBot_Payload::CHECK_ORDER:
      		break;

      	case Omise_FBBot_Payload::HELP:
      		break;

      	default:
          # Custom payload :
          self::handle_custom_payload( $sender_id, $payload ); 
          break;
      }
    }

    private static function handle_custom_payload( $sender_id, $payload ) {
      error_log( 'handle_custom_payload : ' . $payload );
    }
}