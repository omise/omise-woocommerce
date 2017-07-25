<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if( ! class_exists( 'FB_Message_Item' ) ) {
	class FB_Message_Item {
		static public function create( $text ) {
			return array('text' => $text);
		}
	}

}

if( ! class_exists( 'FB_File_Message_Item' ) ) {
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

}

if( ! class_exists( 'FB_Image_Message_Item' ) ) {
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
	
}