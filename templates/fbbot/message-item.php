<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'FB_Message_Item' ) ) {
	return;
}

class FB_Message_Item {
	static public function create( $text ) {
		return array( 'text' => $text );
	}
}