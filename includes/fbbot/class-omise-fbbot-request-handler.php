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
			$message = strtolower( $user_message );
      error_log( 'handle message : ' . $message );
    }

    public static function handle_payload_from( $sender_id, $payload ) {
      error_log( 'handle payload : ' . $payload );
    }

    private static function handle_custom_payload( $sender_id, $payload ) {
      error_log( 'handle_custom_payload : ' . $payload );
    }
}