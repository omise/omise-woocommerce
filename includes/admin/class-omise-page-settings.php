<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Page_Settings' ) ) {
	return;
}

class Omise_Page_Settings {
	/**
	 * @var Omise_Setting
	 */
	protected $settings;

	/**
	 * @since 3.1
	 */
	public function __construct() {
		$this->settings = Omise()->settings();
	}

	/**
	 * @return array
	 *
	 * @since  3.1
	 */
	protected function get_settings() {
		return $this->settings->get_settings();
	}

	/**
	 * @param array $data
	 *
	 * @since  3.1
	 */
	protected function save( $data ) {
		if ( ! isset( $data['omise_setting_page_nonce'] ) || ! wp_verify_nonce( $data['omise_setting_page_nonce'], 'omise-setting' ) ) {
			wp_die( __( 'You are not allowed to modify the settings from a suspicious source.', 'omise' ) );
		}

		$this->settings->update_settings( $data );
	}

	/**
	 * @since  3.1
	 */
	public static function render() {
		global $title;

		$page = new self;

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			$page->save( $_POST );
		}

		$settings = $page->get_settings();

		include_once __DIR__ . '/views/omise-page-settings.php';
	}
}
