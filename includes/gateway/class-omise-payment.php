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
	 * @see omise/includes/class-omise-setting.php
	 *
	 * @var Omise_Setting
	 */
	protected $omise_settings;

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
		'EUR' => 100,
		'GBP' => 100,
		'JPY' => 1,
		'SGD' => 100,
		'THB' => 100,
		'USD' => 100
	);

	/**
	 * @var Omise_Order|null
	 */
	protected $order;

	public function __construct() {
		$this->omise_settings   = Omise()->settings();
		$this->payment_settings = $this->omise_settings->get_settings();
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
		return $this->omise_settings->is_test();
	}

	/**
	 * Return Omise public key.
	 *
	 * @return string
	 */
	protected function public_key() {
		return $this->omise_settings->public_key();
	}

	/**
	 * Return Omise secret key.
	 *
	 * @return string
	 */
	protected function secret_key() {
		return $this->omise_settings->secret_key();
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
	 * @param  array $params
	 *
	 * @return OmiseCharge
	 */
	public function sale( $params ) {
		return OmiseCharge::create( $params );
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
			$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order() );

			if ( ! $this->order()->get_transaction_id() ) {
				/** backward compatible with WooCommerce v2.x series **/
				if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
					$this->order()->set_transaction_id( $charge['id'] );
					$this->order()->save();
				} else {
					update_post_meta( $this->order()->id, '_transaction_id', $charge['id'] );
				}
			}

			if ( 'failed' === $charge['status'] ) {
				$this->order()->add_order_note(
					sprintf(
						wp_kses(
							__( 'Omise: Payment failed.<br/>%s (code: %s) (manual sync).', 'omise' ),
							array( 'br' => array() )
						),
						$charge['failure_message'],
						$charge['failure_code']
					)
				);
				$this->order()->update_status( 'failed' );
				return;
			}

			if ( 'pending' === $charge['status'] ) {
				$this->order()->add_order_note(
					wp_kses(
						__( 'Omise: Payment is still in progress.<br/>You might wait for a moment before click sync the status again or contact Omise support team at support@omise.co if you have any questions (manual sync).', 'omise' ),
						array( 'br' => array() )
					)
				);
				return;
			}

			if ( 'successful' === $charge['status'] ) {
				$this->order()->add_order_note(
					sprintf(
						wp_kses(
							__( 'Omise: Payment successful.<br/>An amount %1$s %2$s has been paid (manual sync).', 'omise' ),
							array( 'br' => array() )
						),
						$this->order()->get_total(),
						$this->order()->get_order_currency()
					)
				);

				if ( ! $this->order()->is_paid() ) {
					$this->order()->payment_complete();
				}

				return;
			}

			throw new Exception(
				__( 'Cannot read the payment status. Please try sync again or contact Omise support team at support@omise.co if you have any questions.', 'omise' )
			);
		} catch ( Exception $e ) {
			$order->add_order_note(
				sprintf(
					wp_kses(
						__( 'Omise: Sync failed (manual sync).<br/>%s (manual sync).', 'omise' ),
						array( 'br' => array() )
					),
					$e->getMessage()
				)
			);
		}
	}

	/**
	 * Set an order transaction id
	 *
	 * @param string $transaction_id  Omise charge id.
	 */
	protected function set_order_transaction_id( $transaction_id ) {
		/** backward compatible with WooCommerce v2.x series **/
		if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
			$this->order()->set_transaction_id( $transaction_id );
			$this->order()->save();
		} else {
			update_post_meta( $this->order()->id, '_transaction_id', $transaction_id );
		}
	}

	/**
	 * Retrieve an attached charge id.
	 *
	 * @deprecated 3.4  We can simply retrieve Omise charge id via WC_Order::get_transaction_id().
	 *                  Unfortunately, we may need to leave this code
	 *                  as it is for backward compatibility reason.
	 *
	 * @return    string
	 */
	public function get_charge_id_from_order() {
		if ( $charge_id = $this->order()->get_transaction_id() ) {
			return $charge_id;
		}

		/**
		 * @deprecated 3.4
		 * The following code are for backward compatible only.
		 */
		// Backward compatible for Omise v3.0 - v3.3
		$order_id  = version_compare( WC()->version, '3.0.0', '>=' ) ? $this->order()->get_id() : $this->order()->id;
		$charge_id = get_post_meta( $order_id, self::CHARGE_ID, true );

		// Backward compatible for Omise v1.2.3
		if ( empty( $charge_id ) ) {
			$charge_id = $this->deprecated_get_charge_id_from_post();
		}

		return $charge_id;
	}

	/**
	 * Attach a charge id into an order.
	 *
	 * @deprecated 3.4  Now using Omise_Payment::set_order_transaction_id().
	 *                  However, keeping this method here just in case
	 *                  if this method has been implemented in some other of 3rd-party plugins.
	 *
	 * @param      string $charge_id  Omise charge id.
	 */
	public function attach_charge_id_to_order( $charge_id ) {
		$this->set_order_transaction_id( $charge_id );
	}

	/**
	 * Retrieve a charge id from a post.
	 *
	 * @deprecated 3.0  No longer assign a new charge id with new post.
	 *
	 * @return     string
	 */
	protected function deprecated_get_charge_id_from_post() {
		/** backward compatible with WooCommerce v2.x series **/
		$order_id  = version_compare( WC()->version, '3.0.0', '>=' ) ? $this->order()->get_id() : $this->order()->id;

		$posts = get_posts(
			array(
				'post_type'  => 'omise_charge_items',
				'meta_query' => array(
					array(
						'key'     => '_wc_order_id',
						'value'   => $order_id,
						'compare' => '='
					)
				)
			)
		);

		if ( empty( $posts ) ) {
			return '';
		}

		$post  = $posts[0];
		$value = get_post_custom_values( '_omise_charge_id', $post->ID );

		if ( ! is_null( $value ) && ! empty( $value ) ) {
			return $value[0];
		}
	}
}
