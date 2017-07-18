<?php
defined('ABSPATH') or die("No direct script access allowed.");

if ( ! class_exists( 'FB_URL_Button_Item' ) ) {
	class FB_URL_Button_Item {
		const RATIO_FULL = 'full';
		const RATIO_COMPACT = 'compact';

		public static function create( $title, $url, $ratio = self::RATIO_FULL, $messenger_extensions = NULL, $fallback_url = NULL ) {
			return new FB_URL_Button_Item( $title, $url, $ratio, $messenger_extensions, $fallback_url );
		}

		public function __construct( $title, $url, $ratio = self::RATIO_FULL, $messenger_extensions = NULL, $fallback_url = NULL ) {
			$this->title = $title;
			$this->url = $url;
			$this->ratio = $ratio;
			$this->messenger_extensions = $messenger_extensions;
			$this->fallback_url = $fallback_url;
		}

		public function get_data() {
			$button = array(
				'type' => 'web_url',
				'url' => $this->url,
				'title' => $this->title,
				'webview_height_ratio' => $this->ratio
			);

			if ( $this->messenger_extensions != NULL ) {
				$button['messenger_extensions'] = $this->messenger_extensions;
			}

			if ( $this->fallback_url != NULL ) {
				$button['fallback_url'] = $this->fallback_url;
			}

			return $button;
		}
	}

}

if ( ! class_exists( 'FB_Postback_Button_Item' ) ) {
	class FB_Postback_Button_Item {
		public static function create( $title, $payload ) {
			return new FB_Postback_Button_Item( $title, $payload );
		}

		public function __construct( $title, $payload ) {
			$this->title = $title;
			$this->payload = $payload;
		}

		public function get_data() {
			return array(
				'type' => 'postback',
				'title' => $this->title,
				'payload' => $this->payload
			);
		}
	}

}

if ( ! class_exists( 'FB_Call_Button_Item' ) ) {
	class FB_Call_Button_Item {
		public static function create( $title, $phone_number ) {
			return new FB_Call_Button_Item( $title, $phone_number );
		}

		public function __construct( $title, $phone_number ) {
			$this->title = $title;
			$this->phone_number = $phone_number;
		}

		public function get_data() {
			return array(
				'type' => 'phone_number',
				'title' => $this->title,
				'payload' => $this->phone_number
			);
		}
	}

}

if ( ! class_exists( 'FB_Share_Button_Item' ) ) {
	class FB_Share_Button_Item {
		public static function create() {
			return new FB_Share_Button_Item();
		}

		public function get_data() {
			return array(
				'type' => 'element_share'
			);
		}
	}

}

if ( ! class_exists( 'FB_Login_Button_Item' ) ) {
	class FB_Login_Button_Item {
		public static function create( $url ) {
			return new FB_Login_Button_Item( $url );
		}

		public function __construct( $url ) {
			$this->url = $url;
		}

		public function get_data() {
			return array(
				'type' => 'account_link',
				'url' => $this->url
			);
		}
	}

}

if ( ! class_exists( 'FB_Logout_Button_Item' ) ) {
	class FB_Logout_Button_Item {
		public static function create() {
			return new FB_Logout_Button_Item();
		}

		public function get_data() {
			return array(
				'type' => 'account_unlink'
			);
		}
	}

}
