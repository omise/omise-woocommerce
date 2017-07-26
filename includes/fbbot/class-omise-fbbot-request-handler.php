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
			$message_store = Omise_FBBot_Message_Store;
			$message = strtolower( $user_message );
      error_log( 'handle message : ' . $message );

      if ( $message_store::check_greeting_words( $message ) ) {
      	$message = Omise_FBBot_Conversation_Generator::greeting_message( $sender_id );
    		$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $message );

      } else if ( $message_store::check_helping_words( $message ) ) {
      	$helping_message = Omise_FBBot_Conversation_Generator::helping_message();
    		$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $helping_message );

      } else if ( $message_store::check_order_checking( $message ) ) {
        // Checking order status from order number
        $checking_text = explode('#', $message);

        if ( ! $checking_text[1] ) {
        	$rechecking_order_message = Omise_FBBot_Conversation_Generator::rechecking_order_number_message();
        	$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $rechecking_order_message );
          return;
        }

        $order_id = $checking_text[1];
        $order_status_message = Omise_FBBot_Conversation_Generator::get_ordet_status_message( $order_id );
        $response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $order_status_message );

      } else {
      	// Handle unrecognize message
      	$unrecognize_message = Omise_FBBot_Conversation_Generator::unrecognized_message();
      	$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $unrecognize_message );
      }
    }

    public static function handle_payload_from( $sender_id, $payload ) {
      error_log( 'handle payload : ' . $payload );
      switch ( $payload ) {
      	case Omise_FBBot_Payload::GET_START_CLICKED:
      		$message = Omise_FBBot_Conversation_Generator::greeting_message( $sender_id );
    			$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $message );
      		break;

      	case Omise_FBBot_Payload::FEATURE_PRODUCTS:
      		$message = Omise_FBBot_Conversation_Generator::feature_products_message( $sender_id );
    			$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $message );
      		break;

      	case Omise_FBBot_Payload::PRODUCT_CATEGORY:
      		$message = Omise_FBBot_Conversation_Generator::product_category_message( $sender_id );
    			$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $message );
      		break;

      	case Omise_FBBot_Payload::CHECK_ORDER:
      		$message = Omise_FBBot_Conversation_Generator::before_checking_order_message( $sender_id );
    			$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $message );
      		break;

      	case Omise_FBBot_Payload::HELP:
      		$helping_message = Omise_FBBot_Conversation_Generator::helping_message( $sender_id );
    			$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $helping_message );
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
        $product_gallery_message = Omise_FBBot_Conversation_Generator::product_gallery_message( $sender_id, $product_id );

        $response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $product_gallery_message );

      } else if ($explode[0] == 'VIEW_CATEGORY_PRODUCTS') {
        $category_slug = $explode[1];
        $products_list_message = Omise_FBBot_Conversation_Generator::product_list_in_category_message( $sender_id, $category_slug );

        $result = Omise_FBBot_HTTPService::send_message_to( $sender_id, $products_list_message );
      } else {
      	// unexpected payload
      }
    }

    public static function handle_triggered_from_omise( $request ) {
      error_log("handle_triggered_from_omise");
      $body = json_decode( $request->get_body() );

      if ( ( ! isset( $body ) ) || ( ! isset( $body->data ) ) ) 
        return;

      $charge = $body->data;
      $metadata = $charge->metadata;
    
      if ( ! isset( $metadata ) || ! isset( $metadata->source ) ||  $metadata->source != 'woo_omise_bot' ) {
        // NOTE: Ignore from other source, allow only woocommerece bot
        return;
      }

      $event_name = $body->key;
      
      switch ( $event_name ) {
        case 'charge.create':
          error_log('charge.create');
          // Charge has been created
          if ( $charge->return_uri ) {
            // Ignore case of 3ds enable for normal charge
            return;
          } 

          // Update order status here!
          $order_id = $metadata->order_id;
          Omise_FBBot_WooCommerce::update_order_status( $order_id, $charge );

          $sender_id = $charge->metadata->messenger_id;
          if( ! isset( $sender_id ) )
            return;

          $thanks_message = Omise_FBBot_Conversation_Generator::thanks_for_purchase_message( $order_id );
          
          $response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $thanks_message );
          break;

        case 'charge.complete':
          error_log('charge.complete');
          // Complete charge (only for 3D-Secure charge and Internet Banking)
          // Should query charge from omise api and check again : 'charge->paid' = 1 is success for make sure
          // $charge = OmiseCharge::retrieve( $id, '', $this->secret_key );
          // if ( ! OmisePluginHelperCharge::isPaid( $charge ) )
          // handle $charge['failure_message']
          // if success go to thanks page, if fail go to error page( or send success||fail message)

          // Update order status here!
          $order_id = $metadata->order_id;
          Omise_FBBot_WooCommerce::update_order_status( $order_id, $charge );

          // 
          $sender_id = $charge->metadata->messenger_id;
          if( ! isset( $sender_id ) )
            return;

          $thanks_message = Omise_FBBot_Conversation_Generator::thanks_for_purchase_message( $order_id );
          
          $response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $thanks_message );
          break;
        
        default:
          error_log( $event_name .' is not create or complete charge event we ignore this case.' );
          return false;
          break;
      }
    }
}