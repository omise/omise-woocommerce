<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if( class_exists( 'FB_Button_Template' ) ) {
	return;
}

class FB_Button_Template {
	static public function create( $text, $buttons ) {
		return array(
		'attachment' => array(
				'type' => 'template',
				'payload' => array(
					'template_type' => 'button',
					'text' => $text,
					'buttons' => $buttons
				)
			)
		);
	}
}

