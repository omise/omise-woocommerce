<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'FB_Generic_Template' ) ) {
	return;
}

class FB_Generic_Template {
	static public function create( $elements ) {
		return array(
		'attachment' => array(
					'type' => 'template',
					'payload' => array(
						'template_type' => 'generic',
						'elements' => $elements
					)
				)
		);
	}
}
