<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_installment() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'Omise_Payment_Installment' ) ) {
		return;
	}

	/**
	 * @since 3.4
	 */
	class Omise_Payment_Installment extends Omise_Payment {
		public function __construct() {
			parent::__construct();

			$this->id                 = 'omise_installment';
			$this->has_fields         = true;
			$this->method_title       = __( 'Omise Installment', 'omise' );
			$this->method_description = wp_kses(
				__( 'Accept payment through <strong>Installment</strong> via Omise payment gateway.', 'omise' ),
				array( 'strong' => array() )
			);

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'wp_enqueue_scripts', array( $this, 'omise_assets' ) );
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
					'label'   => __( 'Enable Omise Installment Payment', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'omise' ),
					'default'     => __( 'Installment', 'omise' ),
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
		public function payment_fields() {
			$provider_names = array(
				'installment_bay'          => __( 'Krungsri', 'omise' ),
				'installment_first_choice' => __( 'Krungsri First Choice', 'omise' ),
				'installment_kbank'        => __( 'Kasikorn Bank', 'omise' ),
				'installment_bbl'          => __( 'Bangkok Bank', 'omise' ),
				'installment_ktc'          => __( 'Krungthai Card (KTC)', 'omise' ),
			);

			$currency             = get_woocommerce_currency();
			$cart_total           = WC()->cart->total;
			$capabilities         = Omise_Capabilities::retrieve();
			$installment_backends = $capabilities->getInstallmentBackends( $currency, $this->format_amount_subunit( $cart_total, $currency ) );

			foreach ( $installment_backends as &$backend ) {
				$backend->provider_code = str_replace( 'installment_', '', $backend->_id );
				$backend->provider_name = isset( $provider_names[ $backend->_id ] ) ? $provider_names[ $backend->_id ] : strtoupper( $backend->provider_code );
			}

			Omise_Util::render_view(
				'templates/payment/form-installment.php',
				array(
					'installment_backends' => $installment_backends
				)
			);
		}

		/**
		 * Register all required javascripts
		 */
		public function omise_assets() {
			if ( ! is_checkout() || ! $this->is_available() ) {
				return;
			}

			wp_enqueue_style( 'omise-css', plugins_url( '../../assets/css/omise-css.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );
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

	if ( ! function_exists( 'add_omise_installment' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_installment( $methods ) {
			$methods[] = 'Omise_Payment_Installment';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_installment' );
	}
}
