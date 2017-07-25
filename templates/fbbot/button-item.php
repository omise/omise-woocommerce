<?php
defined('ABSPATH') or die("No direct script access allowed.");

if ( ! class_exists( 'FB_URL_Button_Item' ) ) {
	class FB_URL_Button_Item {
		const RATIO_FULL = 'full';
		const RATIO_COMPACT = 'compact';

		public static function create( $title, $url, $ratio = self::RATIO_FULL, $messenger_extensions = NULL, $fallback_url = NULL ) {
			$button = array(
				'type' => 'web_url',
				'url' => $url,
				'title' => $title,
				'webview_height_ratio' => $ratio
			);

			if ( $messenger_extensions != NULL ) {
				$button['messenger_extensions'] = $messenger_extensions;
			}

			if ( $fallback_url != NULL ) {
				$button['fallback_url'] = $fallback_url;
			}

			return $button;
		}
	}

}

if ( ! class_exists( 'FB_Postback_Button_Item' ) ) {
	class FB_Postback_Button_Item {
		public static function create( $title, $payload ) {
			return array(
				'type' => 'postback',
				'title' => $title,
				'payload' => $payload
			);
		}
	}

}

if ( ! class_exists( 'FB_Call_Button_Item' ) ) {
	class FB_Call_Button_Item {
		public static function create( $title, $phone_number ) {
			return array(
				'type' => 'phone_number',
				'title' => $title,
				'payload' => $phone_number
			);
		}
	}

}

if ( ! class_exists( 'FB_Share_Button_Item' ) ) {
	class FB_Share_Button_Item {
		public static function create() {
			return array(
				'type' => 'element_share'
			);
		}
	}

}

if ( ! class_exists( 'FB_Login_Button_Item' ) ) {
	class FB_Login_Button_Item {
		public static function create( $url ) {
			return array(
				'type' => 'account_link',
				'url' => $url
			);
		}
	}

}

if ( ! class_exists( 'FB_Logout_Button_Item' ) ) {
	class FB_Logout_Button_Item {
		public static function create() {
			return array(
				'type' => 'account_unlink'
			);
		}
	}

}
