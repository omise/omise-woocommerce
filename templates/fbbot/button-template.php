<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if( ! class_exists( 'FB_Button_Template' )){
	class FB_Button_Template {
		static public function create( $text, $buttons ) {
			return new FB_Button_Template( $text, $buttons );
		}

		public function __construct( $text, $buttons ) {
			$this->text = $text;
			$this->buttons = $buttons;
		}

		public function get_data() {
			return array(
			'attachment' => array(
					'type' => 'template',
					'payload' => array(
						'template_type' => 'button',
						'text' => $this->text,
						'buttons' => $this->buttons
					)
				)
			);
		}
	}
}