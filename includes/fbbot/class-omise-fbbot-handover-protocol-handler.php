<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Handover_Protocol_Handler' ) ) {
	return;
}

class Omise_FBBot_Handover_Protocol_Handler {
	public static function switch_to_live_agent( $sender_id ) {
		$page_inbox_app_id = "263902037430900";

		$body = array(
			"recipient" => array( "id" => $sender_id ),
			"target_app_id" => $page_inbox_app_id
		);

		$url = Omise_FBBot_Configurator::get_fb_pass_thread_endpoint();
		$response = Omise_FBBot_HTTPService::send_request( $url, $body );

		$body = json_decode($response['body']);

		if ( $body->success ) {
			return true;
		}

		return false;
	}
}