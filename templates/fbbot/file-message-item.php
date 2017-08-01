<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'FB_File_Message_Item' ) ) {
  return;
}

class FB_File_Message_Item {
	static public function create( $url ) {
		return array(
			'attachment' => array(
					'type' => 'file',
					'payload' => array(
						'url' => $url
					)
				)
			);
	}
}