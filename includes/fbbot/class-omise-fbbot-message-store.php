<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if( ! class_exists( 'Omise_Messenger_Bot_Message_Store' ) ) {
	class Omise_Messenger_Bot_Message_Store {
		private function __construct() {
			// Hide the constructor
		}

		public static function get_greeting_message( $sender_id  ) {
			$user = Omise_Messenger_Bot_User_Service::get_user( $sender_id );
			$shop_name = get_bloginfo( 'name' );

			$greeting_message_1 = Omise_Util::translate( ':D Hello ' ) . $user->display_name . ' ';
			$greeting_message_2 = Omise_Util::translate( 'Welcome to ') . $shop_name;
			$greeting_message_3 = Omise_Util::translate( ', what are you looking for today ?' );

			$greeting_message = $greeting_message_1 . $greeting_message_2 . $greeting_message_3;

			return $greeting_message;
		}

		public static function get_helping_message() {
			$helping_message = Omise_Util::translate( ":) Don't worry, in spite of the fact i'm just a bot but i can help you. You can choose 1 choice from below." );

			return $helping_message;
		}

		public static function get_unrecognized_message() {
			$default_message_1 = Omise_Util::translate( ":'(  I wish I could understand you, maybe one day! I’m here to help you shopping on Messenger app, Do you want to buy something ?" );
			$default_message_2 = Omise_Util::translate( '🤖  Oh, I’m just a bot! but i have a cool stuff for cool people like you. Which do you like best ?' );

			$default_messages = array( $default_message_1, $default_message_2 );

			$default_message = $default_messages[ mt_rand( 0, count( $default_messages ) - 1 ) ];

			return $default_message;
		}

	}
}