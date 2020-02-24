<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_paynow() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'Omise_Payment_Paynow' ) ) {
		return;
	}

	/**
	 * @since 3.11
	 */
	class Omise_Payment_Paynow extends Omise_Payment {
		public function __construct() {
			parent::__construct();

			$this->id                 = 'omise_paynow';
			$this->has_fields         = false;
			$this->method_title       = __( 'Omise PayNow', 'omise' );
			$this->method_description = wp_kses(
				__( 'Accept payments through <strong>PayNow</strong> via Omise payment gateway.', 'omise' ),
				array( 'strong' => array() )
			);

			$this->init_form_fields();
			$this->init_settings();

			$this->title                = $this->get_option( 'title' );
			$this->description          = $this->get_option( 'description' );
			$this->restricted_countries = array( 'SG' );

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
					'label'   => __( 'Enable Omise PayNow Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'PayNow', 'omise' ),
				),

				'description' => array(
					'title'       => __( 'Description', 'omise' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description the user sees during checkout.', 'omise' )
				),
			);
		}

		/**
		 * @inheritdoc
		 */
		public function charge( $order_id, $order ) {
			// ...
		}

		/**
		 * @inheritdoc
		 */
		public function result( $order_id, $order, $charge ) {
			// ...
		}
	}

	if ( ! function_exists( 'add_omise_paynow' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_paynow( $methods ) {
			$methods[] = 'Omise_Payment_Paynow';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_paynow' );
	}
}
