<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'FB_Image_Message_Item' ) ) {
  return;
}

class FB_Image_Message_Item {
	static public function create( $url ) {
		return array(
			'attachment' => array(
					'type' => 'image',
					'payload' => array(
						'url' => $url
					)
				)
			);
	}
}
