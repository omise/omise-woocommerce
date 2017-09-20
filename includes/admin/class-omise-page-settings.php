<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Page_Settings' ) ) {
	return;
}

class Omise_Page_Settings {
	public $slug = 'omise-settings';

	/**
	 * @var Omise_Setting
	 */
	protected $settings;

	/**
	 * @since 3.1
	 */
	public function __construct() {
		$this->settings = new Omise_Setting;
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

		include_once __DIR__ . '/views/omise-page-settings.php';
	}

	/**
	 * Render menu in the setting page.
	 *
	 * @since 3.2
	 */
	protected function render_menu() {
		echo '
			<a href="' . $this->payment_setting_url() . '" class="nav-tab ' . ( ( '' === $this->current_tab() ) ? 'nav-tab-active' : '' ) . '">' . __( 'Payment Settings', 'omise' ) . '</a>
			<a href="' . $this->chatbot_setting_url() . '" class="nav-tab ' . ( ( 'chatbot' === $this->current_tab() ) ? 'nav-tab-active' : '' ) . '">' . __( 'Chatbot', 'omise' ) . '</a>
		';
	}

	/**
	 * Render content in the setting page.
	 *
	 * @since 3.2
	 */
	protected function render_content() {
		$tab = 'render_tab_' . ( $this->current_tab() ?: 'payment' );

		$this->$tab();
	}

	/**
	 * Render setting page, tab 'payment'.
	 *
	 * @since 3.2
	 */
	protected function render_tab_payment() {
		$settings = $this->get_settings();

		include_once __DIR__ . '/views/omise-page-payment-settings.php';
	}

	/**
	 * Render setting page, tab 'chatbot'.
	 *
	 * @since 3.2
	 */
	protected function render_tab_chatbot() {
		$settings = $this->get_settings();

		include_once __DIR__ . '/views/omise-page-chatbot-settings.php';
	}

	/**
	 * Get current 'tab' value from querystring.
	 *
	 * @return string
	 *
	 * @since  3.2
	 */
	protected function current_tab() {
		return isset( $_GET['tab'] ) ? $_GET['tab'] : '';
	}

	/**
	 * Return of Payment settings page uri
	 * uri: admin.php?page=omise-settings
	 *
	 * @return string
	 *
	 * @since  3.2
	 */
	protected function payment_setting_url() {
		return esc_url(
			add_query_arg(
				array( 'page' => $this->slug ),
				admin_url( 'admin.php' )
			)
		);
	}

	/**
	 * Return of Chatbot settings page uri
	 * uri: admin.php?page=omise-settings&tab=chatbot
	 *
	 * @return string
	 *
	 * @since  3.2
	 */
	protected function chatbot_setting_url() {
		return esc_url(
			add_query_arg(
				array( 'page' => $this->slug, 'tab' => 'chatbot' ),
				admin_url( 'admin.php' )
			)
		);
	}
}
