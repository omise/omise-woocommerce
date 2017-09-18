<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Handover_Protocol_Handler' ) ) {
	return;
}

class Omise_FBBot_Handover_Protocol_Handler {
	public static function get_second_receiver() {
		$url = Omise_FBBot_Configurator::get_fb_secondary_receivers_endpoint();
		$response = Omise_FBBot_HTTPService::send_get_request( $url );
		$body = json_decode($response['body']);
		error_log(print_r($body->data[0]->id, true));

		return $body->data[0]->id;
	}

	public static function pass_thread_to( $psid, $target_app_id ) {
		$body = array(
			"recipient" => array( "id" => $psid),
			"target_app_id" => $target_app_id
		);

		error_log(print_r($body, true));

		$url = Omise_FBBot_Configurator::get_fb_pass_thread_endpoint();
		$response = Omise_FBBot_HTTPService::send_request( $url, $body );

		error_log(print_r($response, true));
	}

	public static function switch_to_live_agent( $sender_id, $recipient_id ) {

	}

	public static function switch_to_bot( $sender_id, $recipient_id ) {

	}
}