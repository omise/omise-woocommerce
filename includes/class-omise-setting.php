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
			'sandbox'          => 'yes',
			'test_public_key'  => '',
			'test_private_key' => '',
			'live_public_key'  => '',
			'live_private_key' => ''
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
	 * @param  array $data
	 *
	 * @return array
	 *
	 * @since  3.1
	 */
	public function update_settings( $data ) {
		$data['sandbox'] = ! is_null( $data['sandbox'] ) ? 'yes' : 'no';
		
		$this->settings = array_merge(
			$this->settings,
			$data
		);

		update_option( $this->get_payment_method_settings_name( 'omise' ), $this->settings );
	}

	/**
	 * Returns the payment gateway settings option name
	 *
	 * @param  string $payment_method_id
	 *
	 * @return string The payment gateway settings option name.
	 *
	 * @since  3.0
	 */
	protected function get_payment_method_settings_name( $payment_method_id = 'omise' ) {
		return 'woocommerce_' . $payment_method_id . '_settings';
	}

	/**
	 * @param  string $id
	 *
	 * @return array
	 *
	 * @since  3.0
	 */
	public function get_payment_settings( $id ) {
		if ( $options = get_option( $this->get_payment_method_settings_name( $id ) ) ) {
			return array_merge(
				$this->get_default_settings(),
				$options
			);
		}

		return $this->get_default_settings();
	}

	/**
	 * Whether Sandbox (test) mode is enabled or not.
	 *
	 * @return bool
	 */
	public function is_test() {
		$sandbox = $this->settings['sandbox'];

		return isset( $sandbox ) && $sandbox == 'yes';
	}

	/**
	 * Return Omise public key.
	 *
	 * @return string
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
	 */
	public function secret_key() {
		if ( $this->is_test() ) {
			return $this->settings['test_private_key'];
		}

		return $this->settings['live_private_key'];
	}
}
