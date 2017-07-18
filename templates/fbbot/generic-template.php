<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'FB_Generic_Template' ) ) {
	class FB_Generic_Template {
		static public function create( $elements ) {
			return new FB_Generic_Template( $elements );
		}

		public function __construct( $elements ) {
			$this->elements = $elements;
		}

		public function get_data() {
			return array(
			'attachment' => array(
						'type' => 'template',
						'payload' => array(
							'template_type' => 'generic',
							'elements' => $this->elements
						)
					)
			);
		}
	}

}