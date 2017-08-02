<?php
defined('ABSPATH') or die("No direct script access allowed.");

if ( class_exists( 'FB_URL_Button_Item' ) ) {
	return;
}

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