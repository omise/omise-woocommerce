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
			$this->method_title       = __( 'Omise Installments', 'omise' );
			$this->method_description = wp_kses(
				__( 'Accept <strong>installment payments</strong> via Omise payment gateway.', 'omise' ),
				array( 'strong' => array() )
			);

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'wp_enqueue_scripts', array( $this, 'omise_assets' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_api_' . $this->id . '_callback', array( $this, 'callback' ) );
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
					'label'   => __( 'Enable Omise Installment Payments', 'omise' ),
					'default' => 'no'
				),

				'title' => array(
					'title'       => __( 'Title', 'omise' ),
					'type'        => 'text',
					'description' => __( 'This controls the title the user sees during checkout.', 'omise' ),
					'default'     => __( 'Installments', 'omise' ),
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
				array( 'installment_backends' => $installment_backends )
			);
		}

		/**
		 * Register all required css and javascripts.
		 */
		public function omise_assets() {
			if ( is_checkout() && $this->is_available() ) {
				wp_enqueue_style( 'omise-css', plugins_url( '../../assets/css/omise-css.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );
			}
		}

		/**
		 * @inheritdoc
		 */
		public function charge( $order_id, $order ) {
			$source_type       = isset( $_POST['source']['type'] ) ? $_POST['source']['type'] : '';
			$installment_terms = isset( $_POST[ $source_type . '_installment_terms'] ) ? $_POST[ $source_type . '_installment_terms'] : '';
			$metadata          = array_merge(
				apply_filters( 'omise_charge_params_metadata', array(), $order ),
				array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
			);

			return OmiseCharge::create( array(
				'amount'            => $this->format_amount_subunit( $order->get_total(), $order->get_order_currency() ),
				'currency'          => $order->get_order_currency(),
				'description'       => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
				'source'            => array(
					'type'              => sanitize_text_field( $source_type ),
					'installment_terms' => sanitize_text_field( $installment_terms )
				),
				'return_uri'        => add_query_arg( 'order_id', $order_id, site_url() . '?wc-api=omise_installment_callback' ),
				'metadata'          => $metadata
			) );
		}

		/**
		 * @inheritdoc
		 */
		public function result( $order_id, $order, $charge ) {
			if ( self::STATUS_FAILED == $charge['status'] ) {
				return $this->payment_failed( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
			}

			if ( self::STATUS_PENDING == $charge['status'] ) {
				$order->add_order_note( sprintf( __( 'Omise: Redirecting buyer to %s', 'omise' ), esc_url( $charge['authorize_uri'] ) ) );

				return array (
					'result'   => 'success',
					'redirect' => $charge['authorize_uri'],
				);
			}

			return $this->payment_failed(
				sprintf(
					__( 'Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise' ),
					$order_id
				)
			);
		}

		/**
		 * @return void
		 */
		public function callback() {
			if ( ! isset( $_GET['order_id'] ) || ! $order = $this->load_order( $_GET['order_id'] ) ) {
				wc_add_notice(
					wp_kses(
						__( 'We cannot validate your payment result:<br/>Note that your payment might already has been processed. Please contact our support team if you have any questions.', 'omise' ),
						array( 'br' => array() )
					),
					'error'
				);

				wp_redirect( wc_get_checkout_url() );
				exit;
			}

			$order->add_order_note( __( 'Omise: Validating the payment result..', 'omise' ) );

			try {
				$charge = OmiseCharge::retrieve( $order->get_transaction_id() );

				if ( self::STATUS_FAILED == $charge['status'] ) {
					throw new Exception( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
				}

				if ( self::STATUS_SUCCESSFUL == $charge['status'] && $charge['paid'] ) {
					$order->add_order_note(
						sprintf(
							wp_kses(
								__( 'Omise: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' ),
								array( 'br' => array() )
							),
							$order->get_total(),
							$order->get_order_currency()
						)
					);

					$order->payment_complete();

					WC()->cart->empty_cart();

					wp_redirect( $order->get_checkout_order_received_url() );
					exit;
				}

				if ( self::STATUS_PENDING == $charge['status'] && ! $charge['paid'] ) {
					$order->add_order_note(
						wp_kses(
							__( 'Omise: The payment has been processing.<br/>Due to the installment provider, this might takes a few seconds or an hour. Please do a manual \'Sync Payment Status\' action from the Order Actions panel or check the payment status directly at Omise dashboard again later', 'omise' ),
							array( 'br' => array() )
						)
					);

					$order->update_status( 'on-hold' );

					wp_redirect( $order->get_checkout_order_received_url() );
					exit;
				}

				throw new Exception( __( 'Note that your payment may have already been processed. Please contact our support team if you have any questions.', 'omise' ) );
			} catch ( Exception $e ) {
				wc_add_notice(
					sprintf(
						wp_kses(
							__( 'It seems we\'ve been unable to process your payment properly:<br/>%s', 'omise' ),
							array( 'br' => array() )
						),
						$e->getMessage()
					),
					'error'
				);

				$order->add_order_note(
					sprintf(
						wp_kses(
							__( 'Omise: Payment failed.<br/>%s', 'omise' ),
							array( 'br' => array() )
						),
						$e->getMessage()
					)
				);

				$order->update_status( 'failed' );

				wp_redirect( wc_get_checkout_url() );
				exit;
			}

			wp_die( 'Access denied', 'Access Denied', array( 'response' => 401 ) );
			die();
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
