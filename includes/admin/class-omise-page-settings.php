<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Page_Settings' ) ) {
	return;
}

class Omise_Page_Settings {
	protected $settings;

	public function __construct() {
		$this->settings = new Omise_Setting;
	}
	public static function render() {
		global $title;

		$page = new self;

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			$page->save( $_POST );
		}

		$settings = array();
		$settings['payment'] = $page->get_settings();

		include_once __DIR__ . '/views/omise-page-settings.php';
	}

	protected function get_settings() {
		return $this->settings->get_settings();
	}

	protected function save( $data ) {
		$this->settings->update_settings( $data );		
	}
}
