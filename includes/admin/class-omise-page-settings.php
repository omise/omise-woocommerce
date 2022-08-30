<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Page_Settings' ) ) {
	return;
}

class Omise_Page_Settings extends Omise_Admin_Page {
	/**
	 * @param array $data
	 *
	 * @since  3.1
	 */
	protected function save( $data ) {
		if ( ! isset( $data['omise_setting_page_nonce'] ) || ! wp_verify_nonce( $data['omise_setting_page_nonce'], 'omise-setting' ) ) {
			wp_die( __( 'You are not allowed to modify the settings from a suspicious source.', 'omise' ) );
		}

		$public_key = isset( $data['sandbox'] ) ? $data['test_public_key'] : $data['live_public_key'];
		$secret_key = isset( $data['sandbox'] ) ? $data['test_private_key'] : $data['live_private_key'];

		try {
			$account = OmiseAccount::retrieve( $public_key, $secret_key );

			$data['account_id']      = $account['id'];
			$data['account_email']   = $account['email'];
			$data['account_country'] = $account['country'];

			$this->update_settings( $data );
			$this->add_message(
				'message',
				sprintf( __( 'The settings have been saved, an account: %s has been connected.', 'omise' ), $account['email'] )
			);
		} catch (Exception $e) {
			$this->add_message( 'error', $e->getMessage() );
		}
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

		/**
		 * Added later at Omise-WooCommerce v3.11.
		 * To migrate all the users that haven been using Omise-WooCommerce
		 * below the version v3.11.
		 */
		if ( ! $settings['account_country'] && ( $settings['test_private_key'] || $settings['live_private_key'] ) ) {
			$settings['omise_setting_page_nonce'] = wp_create_nonce( 'omise-setting' );
			$page->save( $settings );
			$settings = $page->get_settings();
		}

		include_once __DIR__ . '/views/omise-page-settings.php';
	}
}
