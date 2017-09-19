<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Request_Handler' ) ) {
	return;
}

class Omise_FBBot_Request_Handler {
	
	private function __construct() {
		// Hide the constructor
	}

	public static function handle_message_from( $sender_id, $message ) {
		$bot = new Omise_FBBot_Conversation_Generator();
		$bot->listen( $sender_id, $message );
		$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $bot->reply_for_message() );
	}

	public static function handle_payload_from( $sender_id, $payload ) {
		$bot = new Omise_FBBot_Conversation_Generator();
		$bot->listen_payload( $sender_id, $payload );
		$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $bot->reply_for_payload() );
	}

	public static function handle_callback_omise_webhook( $request ) {
		$body = json_decode( $request->get_body() );
		if ( ( ! isset( $body ) ) || ( ! isset( $body->data ) ) || ( $body->data->object != 'charge' ) ) 
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
				// Charge has been created
				if ( $charge->return_uri ) {
				// Ignore case of 3ds enable for normal charge
				return;
				} 

				// Update state!
				self::handle_state_from_charge( $charge );
				break;

			case 'charge.complete':
				// Complete charge (only for 3D-Secure charge and Internet Banking)

				// Update state!
				self::handle_state_from_charge( $charge );
				break;
			
			default:
				error_log( $event_name .' is not create or complete charge event, we ignore this case.' );
				break;
		}
	}

	private static function handle_state_from_charge( $charge ) {
		$metadata = $charge->metadata;
		$order_id = $metadata->order_id;

		Omise_FBBot_WooCommerce::update_order_status( $order_id, $charge );

		$sender_id = $charge->metadata->messenger_id;
		if( ! isset( $sender_id ) )
			return;

		$message = Omise_FBBot_Conversation_Generator::reply_for_purchase_message( $charge );

		$response = Omise_FBBot_HTTPService::send_message_to( $sender_id, $message );
	}
}