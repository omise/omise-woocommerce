<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Setting' ) ) {
	return;
}

class Omise_Setting {
	/**
	 * @var null | array
	 */
	public $settings;

	/**
	 * @since 3.1
	 */
	public function __construct() {
		$this->settings = $this->get_payment_settings( 'omise' );
	}

	/**
	 * @return array
	 *
	 * @since  3.1
	 */
	protected function get_default_settings() {
		return array(
			'sandbox'                            => 'yes',
			'test_public_key'                    => '',
			'test_private_key'                   => '',
			'live_public_key'                    => '',
			'live_private_key'                   => '',
			'chatbot_enabled'                    => 'no',
			'chatbot_available_time_from'        => '00:00:00',
			'chatbot_available_time_to'          => '23:59:59',
			'chatbot_facebook_page_access_token' => '',
			'chatbot_facebook_bot_verify_token'  => ''
		);
	}

	/**
	 * @return array
	 *
	 * @since  3.1
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Returns the payment gateway settings option name
	 *
	 * @param  string $payment_id  Payment ID can be found at each of gateway classes (includes/gateway).
	 *
	 * @return string              The payment gateway settings option name.
	 *
	 * @since  3.1
	 */
	protected function get_payment_method_settings_name( $payment_id = 'omise' ) {
		return 'woocommerce_' . $payment_id . '_settings';
	}

	/**
	 * Get Omise settings from 'wp_options' table.
	 *
	 * @param  string $payment_id
	 *
	 * @return array
	 *
	 * @since  3.1
	 */
	public function get_payment_settings( $payment_id ) {
		if ( $options = get_option( $this->get_payment_method_settings_name( $payment_id ) ) ) {
			return array_merge(
				$this->get_default_settings(),
				$options
			);
		}

		return $this->get_default_settings();
	}

	/**
	 * @param  array $data
	 *
	 * @return array
	 *
	 * @since  3.1
	 */
	public function update_settings( $data ) {
		// TODO: No hardcode..
		$data['sandbox']         = isset( $data['sandbox'] ) && ! is_null( $data['sandbox'] ) ? 'yes' : 'no';
		$data['chatbot_enabled'] = isset( $data['chatbot_enabled'] ) && ! is_null( $data['chatbot_enabled'] ) ? 'yes' : 'no';

		$this->settings = array_merge(
			$this->settings,
			$data
		);

		update_option( $this->get_payment_method_settings_name( 'omise' ), $this->settings );
	}

	/**
	 * Whether Sandbox (test) mode is enabled or not.
	 *
	 * @return bool
	 *
	 * @since  3.1
	 */
	public function is_test() {
		$sandbox = $this->settings['sandbox'];

		return isset( $sandbox ) && $sandbox == 'yes';
	}

	/**
	 * Return Omise public key.
	 *
	 * @return string
	 *
	 * @since  3.1
	 */
	public function public_key() {
		if ( $this->is_test() ) {
			return $this->settings['test_public_key'];
		}

		return $this->settings['live_public_key'];
	}

	/**
	 * Return Omise secret key.
	 *
	 * @return string
	 *
	 * @since  3.1
	 */
	public function secret_key() {
		if ( $this->is_test() ) {
			return $this->settings['test_private_key'];
		}

		return $this->settings['live_private_key'];
	}
}
