<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if( ! class_exists( 'FB_Message_Item' ) ) {
	class FB_Message_Item {
		static public function create( $text ) {
			return new FB_Message_Item( $text );
		}

		public function __construct( $text ) {
				$this->text = $text;
		}

		public function get_data() {
			return array('text' => $this->text);
		}
	}

}

if( ! class_exists( 'FB_File_Message_Item' ) ) {
	class FB_File_Message_Item {
		static public function create( $url ) {
			return new FB_File_Message_Item( $url );
		}

		public function __construct( $url ) {
			$this->url = $url;
		}

		public function getData() {
			return array(
				'attachment' => array(
						'type' => 'file',
						'payload' => array(
							'url' => $this->url
						)
					)
				);
		}
	}
}

if( ! class_exists( 'FB_Image_Message_Item' ) ) {
	class FB_Image_Message_Item {
		static public function create( $url ) {
			return new FB_Image_Message_Item( $url );
		}

		public function __construct( $url ) {
			$this->url = $url;
		}

		public function getData() {
			return array(
				'attachment' => array(
						'type' => 'image',
						'payload' => array(
							'url' => $this->url
						)
					)
				);
		}
	}
}