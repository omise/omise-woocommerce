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
					'label'   => __( 'Enable Omise Internet Banking Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'omise' ),
					'default'     => __( 'Internet Banking', 'omise' ),
					'desc_tip'    => true,
				),
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
			return array();
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
