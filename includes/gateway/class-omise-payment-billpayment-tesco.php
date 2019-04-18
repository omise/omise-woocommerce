<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_billpayment_tesco() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'Omise_Payment_Billpayment_Tesco' ) ) {
		return;
	}

	/**
	 * @since 3.7
	 */
	class Omise_Payment_Billpayment_Tesco extends Omise_Payment {
		public function __construct() {
			parent::__construct();

			$this->id                 = 'omise_billpayment_tesco';
			$this->has_fields         = false;
			$this->method_title       = __( 'Omise Bill Payment: Tesco', 'omise' );
			$this->method_description = wp_kses(
				__( 'Accept payments through <strong>Tesco Bill Payment</strong> via Omise payment gateway.', 'omise' ),
				array( 'strong' => array() )
			);

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
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
					'label'   => __( 'Enable Omise Tesco Bill Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'omise' ),
					'default'     => __( 'Bill Payment: Tesco', 'omise' ),
				),

				'description' => array(
					'title'       => __( 'Description', 'omise' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which the user sees during checkout.', 'omise' )
				),
			);
		}

		/**
		 * @inheritdoc
		 */
		public function charge( $order_id, $order ) {
			$metadata = array_merge(
				apply_filters( 'omise_charge_params_metadata', array(), $order ),
				array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
			);

			return OmiseCharge::create( array(
				'amount'      => Omise_Money::to_subunit( $order->get_total(), $order->get_order_currency() ),
				'currency'    => $order->get_order_currency(),
				'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
				'source'      => array( 'type' => 'bill_payment_tesco_lotus' ),
				'return_uri'  => add_query_arg( 'order_id', $order_id, site_url() . "?wc-api=omise_billpayment_tesco_callback" ),
				'metadata'    => $metadata
			) );
		}

		/**
		 * @inheritdoc
		 */
		public function result( $order_id, $order, $charge ) {
			if ( 'failed' == $charge['status'] ) {
				return $this->payment_failed( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
			}

			if ( 'pending' == $charge['status'] ) {
				return [
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				];
			}

			return $this->payment_failed(
				sprintf(
					__( 'Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise' ),
					$order_id
				)
			);
		}
	}

	if ( ! function_exists( 'add_omise_billpayment_tesco' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_billpayment_tesco( $methods ) {
			$methods[] = 'Omise_Payment_Billpayment_Tesco';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_billpayment_tesco' );
	}
}
