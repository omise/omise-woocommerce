<?php
defined('ABSPATH') or die("No direct script access allowed.");

if( class_exists( 'FB_Element_Item' ) ) {
	return;
}

class FB_Element_Item {
	static public function create( $title, $subtitle = NULL, $image_url = NULL, $default_action = NULL, $buttons = NULL ) {
		$element = array(
			'title' => $title
		);

		if ($subtitle != NULL) {
			$element['subtitle'] = $subtitle;
		}

		if ($image_url != NULL) {
			$element['image_url'] = $image_url;
		}

		// The default_action behaves like a URL Button and contains the same fields except that the title field is not allowed.
		if ($default_action != NULL) {
			$element['default_action'] = $default_action;
		}

		if ($buttons != NULL) {
			$element['buttons'] = $buttons;
		}

		return $element;
	}
}
