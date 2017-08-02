<?php
defined('ABSPATH') or die("No direct script access allowed.");

if ( class_exists( 'FB_URL_Button_Item' ) ) {
	return;
}

class FB_Postback_Button_Item {
	public static function create( $title, $payload ) {
		return array(
			'type' => 'postback',
			'title' => $title,
			'payload' => $payload
		);
	}
}