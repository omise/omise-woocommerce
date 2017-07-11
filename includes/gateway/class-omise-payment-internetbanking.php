<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_internetbanking() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'Omise_Payment_Internetbanking' ) ) {
		return;
	}

	class Omise_Payment_Internetbanking extends Omise_Payment {
		public function __construct() {
			parent::__construct();

			$this->id                 = 'omise_internetbanking';
			$this->has_fields         = true;
			$this->method_title       = 'Omise Internet Banking';
			$this->method_description = 'Accept payment through Internet Banking (only available in Thailand)';

			$this->init_form_fields();
			$this->init_settings();

			$this->title = $this->get_option( 'title' );

			add_action( 'woocommerce_api_' . $this->id . '_callback', array( $this, 'callback' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'omise_assets' ) );
			add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );
		}

		/**
		 * @see WC_Settings_API::init_form_fields()
		 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'omise' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Omise Internet Banking Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'omise' ),
					'default'     => __( 'Internet Banking', 'omise' ),
					'desc_tip'    => true,
				)
			);
		}

		/**
		 * @see WC_Payment_Gateway::payment_fields()
		 * @see woocommerce/includes/abstracts/abstract-wc-payment-gateway.php
		 */
		public function payment_fields() {
			Omise_Util::render_view( 'templates/payment/form-internetbanking.php', array() );
		}

		/**
		 * @param  int $order_id
		 *
		 * @see    WC_Payment_Gateway::process_payment( $order_id )
		 * @see    woocommerce/includes/abstracts/abstract-wc-payment-gateway.php
		 *
		 * @return array
		 */
		public function process_payment( $order_id ) {
			if ( ! $order = $this->load_order( $order_id ) ) {
				wc_add_notice( __( 'Order not found: ', 'omise' ) . sprintf( 'cannot find order id %s.', $order_id ), 'error' );
				return;
			}

			$order->add_order_note( __( 'Omise: Processing a payment with the Internet Banking..', 'omise' ) );

			try {
				$charge = $this->sale( array(
					'amount'      => $this->format_amount_subunit( $order->get_total(), $order->get_order_currency() ),
					'currency'    => $order->get_order_currency(),
					'description' => 'WooCommerce Order id ' . $order_id,
					'offsite'     => $_POST['omise-offsite'],
					'return_uri'  => add_query_arg( 'order_id', $order_id, site_url() . "?wc-api=omise_internetbanking_callback" )
				) );

				$order->add_order_note( sprintf( __( 'Omise: Charge (id: %s) has been created', 'omise' ), $charge['id'] ) );

				switch ( $charge['status'] ) {
					case 'pending':
						$this->attach_charge_id_to_order( $charge['id'] );

						$order->set_transaction_id( $charge['id'] );
						$order->add_order_note( sprintf( __( 'Omise: Redirecting buyer out to %s', 'omise' ), $charge['authorize_uri'] ) );
						$order->save();

						return array (
							'result'   => 'success',
							'redirect' => $charge['authorize_uri'],
						);
						break;

					case 'failed':
						throw new Exception( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
						break;

					default:
						throw new Exception( __( 'Seems that we cannot process your payment properly. Please try place an order again or contact our support team if you have any questions.', 'omise' ) );
						break;
				}
			} catch ( Exception $e ) {
				wc_add_notice( __( 'Payment failed: ', 'omise' ) . $e->getMessage(), 'error' );

				$order->add_order_note( __( 'Omise: payment failed, ', 'omise' ) . $e->getMessage() );

				return;
			}
		}

		/**
		 * @return void
		 */
		public function callback() {
			if ( ! isset( $_GET['order_id'] ) || ! $order = $this->load_order( $_GET['order_id'] ) ) {
				wc_add_notice( __( 'Order not found: ', 'omise' ) . __( 'Your payment might already has been completed, please contact our support team if you have any questions.', 'omise' ), 'error' );

				header( 'Location: ' . WC()->cart->get_checkout_url() );
				die();
			}

			$order->add_order_note( __( 'Omise: validating a payment result..', 'omise' ) );

			try {
				$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order(), '', $this->secret_key() );

				if ( 'failed' === $charge['status'] ) {
					throw new Exception( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
				}

				if ( 'pending' === $charge['status'] && ! $charge['captured'] ) {
					$order->add_order_note( __( 'Omise: the charge has been pending due to the Bank process. Please check the payment status at Omise dashboard again later', 'omise' ) );
					$order->update_status( 'on-hold' );

					WC()->cart->empty_cart();

					header( 'Location: ' . $order->get_checkout_order_received_url() );
					die();
				}

				if ( 'successful' === $charge['status'] && $charge['captured'] ) {
					$order->add_order_note( sprintf( __( 'Omise: captured an amount %s', 'omise' ), $order->get_total() ) );
					$order->payment_complete();

					WC()->cart->empty_cart();

					header( 'Location: ' . $order->get_checkout_order_received_url() );
					die();
				}

				throw new Exception( __( 'Seems that we cannot process your payment properly. Anyway, your payment might already has been completed, please contact our support team if you have any questions.', 'omise' ) );
			} catch ( Exception $e ) {
				wc_add_notice( __( 'Payment failed: ', 'omise' ) . $e->getMessage(), 'error' );

				$order->add_order_note( __( 'Omise: payment failed, ', 'omise' ) . $e->getMessage() );

				$order->update_status( 'failed' );

				header( 'Location: ' . WC()->cart->get_checkout_url() );
				die();
			}

			wp_die( 'Access denied', 'Access Denied', array( 'response' => 401 ) );
			die();
		}

		/**
		 * Register all javascripts
		 */
		public function omise_assets() {
			if ( ! is_checkout() || ! $this->is_available() ) {
				return;
			}

			wp_enqueue_style( 'omise-payment-form-internetbanking-css', plugins_url( '../../assets/css/payment/form-internetbanking.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );
		}
	}

	if ( ! function_exists( 'add_omise_internetbanking' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_internetbanking( $methods ) {
			$methods[] = 'Omise_Payment_Internetbanking';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_internetbanking' );
	}
}
