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
	 * Payment setting values.
	 *
	 * @var array
	 */
	public $payment_settings = array();

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
		$this->payment_settings = $this->get_payment_settings( 'omise' );
	}

	/**
	 * Returns the array of default payment settings
	 *
	 * @return array of default payment settings
	 */
	protected function get_default_payment_setting_fields() {
		return array(
			'payment_setting' => array(
				'title'       => __( 'Payment Settings', 'omise' ),
				'type'        => 'title',
				'description' => '',
			),

			'sandbox' => array(
				'title'       => __( 'Sandbox', 'omise' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enabling sandbox means that all your transactions will be in TEST mode.', 'omise' ),
				'default'     => 'yes'
			),

			'test_public_key' => array(
				'title'       => __( 'Public key for test', 'omise' ),
				'type'        => 'text',
				'description' => __( 'The "Test" mode public key can be found in Omise Dashboard.', 'omise' )
			),

			'test_private_key' => array(
				'title'       => __( 'Secret key for test', 'omise' ),
				'type'        => 'password',
				'description' => __( 'The "Test" mode secret key can be found in Omise Dashboard.', 'omise' )
			),

			'live_public_key' => array(
				'title'       => __( 'Public key for live', 'omise' ),
				'type'        => 'text',
				'description' => __( 'The "Live" mode public key can be found in Omise Dashboard.', 'omise' )
			),

			'live_private_key' => array(
				'title'       => __( 'Secret key for live', 'omise' ),
				'type'        => 'password',
				'description' => __( 'The "Live" mode secret key can be found in Omise Dashboard.', 'omise' )
			)
		);
	}

	/**
	 * Returns the payment gateway settings option name
	 *
	 * @param  string $payment_method_id
	 *
	 * @return string The payment gateway settings option name.
	 *
	 * @since  2.0
	 */
	protected function get_payment_method_settings_name( $payment_method_id = 'omise' ) {
		return 'woocommerce_' . $payment_method_id . '_settings';
	}

	/**
	 * @param  string $id
	 *
	 * @return array
	 *
	 * @since  2.0
	 */
	public function get_payment_settings( $id ) {
		return get_option( $this->get_payment_method_settings_name( $id ) );
	}

	/**
	 * @param  string|WC_Order $order
	 *
	 * @return Omise_Order|null
	 */
	public function load_order( $order ) {
		if ( $order instanceof WC_Order ) {
			$this->order = $order;
		} else {
			$this->order = wc_get_order( $order );
		}

		if ( ! $this->order ) {
			$this->order = null;
		}

		return $this->order;
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
		$sandbox = $this->payment_settings['sandbox'];

		return isset( $sandbox ) && $sandbox == 'yes';
	}

	/**
	 * Return Omise public key.
	 *
	 * @return string
	 */
	protected function public_key() {
		if ( $this->is_test() ) {
			return $this->payment_settings['test_public_key'];
		}

		return $this->payment_settings['live_public_key'];
	}

	/**
	 * Return Omise secret key.
	 *
	 * @return string
	 */
	protected function secret_key() {
		if ( $this->is_test() ) {
			return $this->payment_settings['test_private_key'];
		}

		return $this->payment_settings['live_private_key'];
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
	 *
	 * @param string $charge_id
	 */
	public function attach_charge_id_to_order( $charge_id ) {
		if ( $this->get_charge_id_from_order() ) {
			delete_post_meta( $this->order()->get_id(), self::CHARGE_ID );
		}

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

	/**
	 * Capture an authorized charge.
	 *
	 * @param  WC_Order $order WooCommerce's order object
	 *
	 * @return void
	 *
	 * @see    WC_Meta_Box_Order_Actions::save( $post_id, $post )
	 * @see    woocommerce/includes/admin/meta-boxes/class-wc-meta-box-order-actions.php
	 */
	public function capture( $order ) {
		$this->load_order( $order );

		try {
			$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order(), '', $this->secret_key() );
			$charge->capture();

			if ( ! OmisePluginHelperCharge::isPaid( $charge ) ) {
				throw new Exception( $charge['failure_message'] );
			}

			$order->add_order_note( sprintf( __( 'Omise: captured the charge id %s ', 'omise' ), $this->get_charge_id_from_order() ) );
			$order->payment_complete();
		} catch ( Exception $e ) {
			$order->add_order_note( __( 'Omise: capture failed, ', 'omise' ) . $e->getMessage() );
		}
	}

	/**
	 * @param  array $params
	 *
	 * @return OmiseCharge
	 */
	public function sale( $params ) {
		return OmiseCharge::create( $params, '', $this->secret_key() );
	}

	/**
	 * Retrieve a charge by a given charge id (that attach to an order).
	 * Find some diff, then merge it back to WooCommerce system.
	 *
	 * @param  WC_Order $order WooCommerce's order object
	 *
	 * @return void
	 *
	 * @see    WC_Meta_Box_Order_Actions::save( $post_id, $post )
	 * @see    woocommerce/includes/admin/meta-boxes/class-wc-meta-box-order-actions.php
	 */
	public function sync_payment( $order ) {
		$this->load_order( $order );

		try {
			$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order(), '', $this->secret_key() );

			if ( 'successful' === $charge['status'] ) {
				$order->add_order_note( sprintf( __( 'Omise: payment successful, an amount %s has been paid', 'omise' ), $order->get_total() ) );

				if ( ! $order->is_paid() ) {
					$order->payment_complete();
				}

				return;
			}

			if ( 'failed' === $charge['status'] ) {
				$order->add_order_note( sprintf( __( 'Omise: payment failed, %s (code: %s)', 'omise' ), $charge['failure_message'], $charge['failure_code'] ) );
				$order->update_status( 'failed' );
				return;
			}

			if ( 'pending' === $charge['status'] ) {
				$order->add_order_note( __( 'Omise: payment is in progress, you might wait for a moment and click sync the status again or contact Omise support team at support@omise.co if you have any questions.', 'omise' ) );
				return;
			}

			throw new Exception( __( 'cannot read the payment status. Please try sync again or or contact Omise support team at support@omise.co if you have any questions.', 'omise' ) );
		} catch ( Exception $e ) {
			$order->add_order_note( sprintf( __( 'Omise: sync failed, %s', 'omise' ), $e->getMessage() ) );
		}
	}
}
