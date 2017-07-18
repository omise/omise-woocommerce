<?php
defined('ABSPATH') or die("No direct script access allowed.");

if( ! class_exists( 'FB_Element_Item' ) ) {
	class FB_Element_Item {
		static public function create( $title, $subtitle = NULL, $image_url = NULL, $default_action = NULL, $buttons = NULL ) {
			return new FB_Element_Item( $title, $subtitle, $image_url, $default_action, $buttons );
		} 

		public function __construct( $title, $subtitle = NULL, $image_url = NULL, $default_action = NULL, $buttons = NULL ) {
			$this->title = $title;
			$this->subtitle = $subtitle;
			$this->image_url = $image_url;
			$this->default_action = $default_action;
			$this->buttons = $buttons;
		}

		public function get_data() {
			$element = array(
				'title' => $this->title
			);

			if ($this->subtitle != NULL) {
				$element['subtitle'] = $this->subtitle;
			}

			if ($this->image_url != NULL) {
				$element['image_url'] = $this->image_url;
			}

			// The default_action behaves like a URL Button and contains the same fields except that the title field is not allowed.
			if ($this->default_action != NULL) {
				$element['default_action'] = $this->default_action;
			}

			if ($this->buttons != NULL) {
				$element['buttons'] = $this->buttons;
			}

			return $element;
		}
	}
}