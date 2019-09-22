<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_truemoney() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'Omise_Payment_Truemoney' ) ) {
		return;
	}

	/**
	 * @since 3.9
	 */
	class Omise_Payment_Truemoney extends Omise_Payment {
		public function __construct() {
			parent::__construct();

			$this->id                 = 'omise_truemoney';
			$this->has_fields         = true;
			$this->method_title       = __( 'Omise TrueMoney Wallet', 'omise' );
			$this->method_description = wp_kses(
				__( 'Accept payments through <strong>TrueMoney Wallet</strong> via Omise payment gateway (only available in Thailand).', 'omise' ),
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
					'label'   => __( 'Enable Omise TrueMoney Wallet Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'TrueMoney Wallet', 'omise' ),
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
		}

		/**
		 * @inheritdoc
		 */
		public function result( $order_id, $order, $charge ) {
		}
	}

	if ( ! function_exists( 'add_omise_truemoney' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_truemoney( $methods ) {
			$methods[] = 'Omise_Payment_Truemoney';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_truemoney' );
	}
}
