<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

if ( class_exists( 'Omise_Payment' ) ) {
	return;
}

abstract class Omise_Payment extends WC_Payment_Gateway {
	/** Omise charge id post meta key. */
	const CHARGE_ID = 'omise_charge_id';

	/**
	 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
	 *
	 * @var string
	 */
	public $id = 'omise';

	/**
	 * @var array
	 */
	private $currency_subunits = array(
		'THB' => 100,
		'JPY' => 1,
		'SGD' => 100,
		'IDR' => 100
	);

	/**
	 * @var Omise_Order|null
	 */
	protected $order;

	public function __construct() {
		$this->define_user_agent();
	}

	/**
	 * @param  string $id
	 *
	 * @return Omise_Order|null
	 */
	public function load_order( $id ) {
		if ( $this->order = wc_get_order( $id ) ) {
			return $this->order;
		}

		return null;
	}

	/**
	 * @return Omise_Order|null
	 */
	public function order() {
		return $this->order;
	}

	/**
	 * Whether Sandbox (test) mode is enabled or not.
	 *
	 * @return bool
	 */
	public function is_test() {
		$sandbox = $this->get_option( 'sandbox' );

		return isset( $sandbox ) && $sandbox == 'yes';
	}

	/**
	 * Return Omise public key.
	 *
	 * @return string
	 */
	protected function public_key() {
		if ( $this->is_test() ) {
			return $this->get_option( 'test_public_key' );
		}

		return $this->get_option( 'live_public_key' );
	}

	/**
	 * Return Omise secret key.
	 *
	 * @return string
	 */
	protected function secret_key() {
		if ( $this->is_test() ) {
			return $this->get_option( 'test_private_key' );
		}

		return $this->get_option( 'live_private_key' );
	}

	/**
	 * @param  string $currency
	 *
	 * @return bool
	 */
	protected function is_currency_support( $currency ) {
		if ( isset( $this->currency_subunits[ strtoupper( $currency ) ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param  int    $amount
	 * @param  string $currency
	 *
	 * @return int
	 */
	protected function format_amount_subunit( $amount, $currency ) {
		if ( isset( $this->currency_subunits[ strtoupper( $currency ) ] ) ) {
			return $amount * $this->currency_subunits[ $currency ];
		}

		return $amount;
	}

	/**
	 * Attach a charge id into an order.
	 */
	public function attach_charge_id_to_order( $charge_id ) {
		add_post_meta( $this->order()->get_id(), self::CHARGE_ID, $charge_id );
	}

	/**
	 * Retrieve an attached charge id.
	 *
	 * @return string
	 */
	public function get_charge_id_from_order() {
		return get_post_meta( $this->order()->get_id(), self::CHARGE_ID, true );
	}

	protected function define_user_agent() {
		global $wp_version;

		$user_agent = sprintf( 'OmiseWooCommerce/%s WordPress/%s WooCommerce/%s', OMISE_WOOCOMMERCE_PLUGIN_VERSION, $wp_version, WC_VERSION );
		defined( 'OMISE_USER_AGENT_SUFFIX' ) || define( 'OMISE_USER_AGENT_SUFFIX', $user_agent );
	}
}
