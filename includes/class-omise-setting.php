<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Setting' ) ) {
	return;
}

class Omise_Setting {
	/**
	 * wp-config.php flag to allow UPA feature rollout for pilot merchants.
	 */
	const FEATURE_UPA_FLAG = 'OMISE_FEATURE_UPA';

	/**
	 * Settings option key for merchant UPA toggle.
	 */
	const OPTION_ENABLE_UPA = 'enable_upa';
	const UPA_API_SCHEME    = 'https';
	const UPA_API_PATH      = '/api';
	const UPA_API_HOST      = 'checkout-page.omise.co';

	/**
	 * The Omise_Setting Instance.
	 *
	 * @since 3.4
	 *
	 * @var   \Omise_Setting
	 */
	protected static $the_instance = null;

	/**
	 * @var null | array
	 */
	public $settings;

	/**
	 * The Omise_Setting Instance.
	 *
	 * @since  3.4
	 *
	 * @static
	 *
	 * @return \Omise_Setting - The instance.
	 */
	public static function instance() {
		if ( is_null( self::$the_instance ) ) {
			self::$the_instance = new self();
		}

		return self::$the_instance;
	}

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
			'account_id'       => '',
			'account_email'    => '',
			'account_country'  => '',
			'sandbox'          => 'yes',
			'test_public_key'  => '',
			'test_private_key' => '',
			'live_public_key'  => '',
			'live_private_key' => '',
			'dynamic_webhook'  => 0,
			self::OPTION_ENABLE_UPA => 0,
			'backends' => null,
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
		$data            = array_intersect_key( $data, $this->get_default_settings() );
		$data['sandbox'] = isset( $data['sandbox'] ) && ! is_null( $data['sandbox'] ) ? 'yes' : 'no';

		array_walk( $data, function( &$input, $key ) {
			$input = is_string($input)?esc_html( sanitize_text_field( $input ) ):$input ;
		} );

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

	public function is_dynamic_webhook_enabled()
	{
		$dynamic_webhook = $this->settings['dynamic_webhook'];
		return (bool)$dynamic_webhook;
	}

	/**
	 * UPA flow is active only when both conditions are true:
	 * 1) wp-config feature flag is enabled.
	 * 2) merchant enabled the toggle in plugin settings.
	 *
	 * @return bool
	 */
	public function is_upa_enabled() {
		return $this->is_upa_feature_flag_enabled() && $this->is_upa_enabled_by_merchant();
	}

	/**
	 * Whether UPA pilot feature is enabled from wp-config.php
	 *
	 * @return bool
	 */
	public function is_upa_feature_flag_enabled() {
		if ( ! defined( self::FEATURE_UPA_FLAG ) ) {
			return false;
		}

		return $this->is_truthy( constant( self::FEATURE_UPA_FLAG ) );
	}

	/**
	 * Whether merchant enabled UPA toggle in plugin settings.
	 *
	 * @return bool
	 */
	public function is_upa_enabled_by_merchant() {
		$enable_upa = isset( $this->settings[ self::OPTION_ENABLE_UPA ] ) ? $this->settings[ self::OPTION_ENABLE_UPA ] : 0;

		return $this->is_truthy( $enable_upa );
	}

	/**
	 * Retrieve UPA API base URL.
	 * Host can be provided from OMISE_UPA_API_BASE_URL env (host only).
	 * When env is missing or invalid, production host is used.
	 *
	 * @return string
	 */
	public function get_upa_api_base_url() {
		$env_host = $this->get_upa_api_host_from_env();
		if ( ! empty( $env_host ) ) {
			return self::UPA_API_SCHEME . '://' . $env_host . self::UPA_API_PATH;
		}

		return self::UPA_API_SCHEME . '://' . self::UPA_API_HOST . self::UPA_API_PATH;
	}

	/**
	 * Resolve UPA host from environment and validate hostname.
	 *
	 * @return string
	 */
	private function get_upa_api_host_from_env() {
		$env_host     = getenv( 'OMISE_UPA_API_BASE_URL' );

		if ( ! is_string( $env_host ) || empty( trim( $env_host ) ) ) {
			return '';
		}

		$env_host = trim( $env_host );

		// Backward-compatible parsing in case a full URL is still provided.
		if ( false !== strpos( $env_host, '://' ) ) {
			$parsed_host = wp_parse_url( $env_host, PHP_URL_HOST );
			if ( is_string( $parsed_host ) && ! empty( $parsed_host ) ) {
				$env_host = $parsed_host;
			}
		}

		$env_host = strtolower( trim( $env_host, " \t\n\r\0\x0B/" ) );
		$env_host = sanitize_text_field( $env_host );

		if ( false === filter_var( $env_host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
			return '';
		}

		// Only allow omise hosts to prevent misconfiguration.
		if ( false === strpos( $env_host, 'omise' ) ) {
			return '';
		}

		return $env_host;
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	private function is_truthy( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_int( $value ) || is_float( $value ) ) {
			return 1 === (int) $value;
		}

		if ( is_string( $value ) ) {
			$normalized = strtolower( trim( $value ) );
			$truthy     = array( '1', 'true', 'yes', 'on' );

			return in_array( $normalized, $truthy, true );
		}

		return false;
	}
}
