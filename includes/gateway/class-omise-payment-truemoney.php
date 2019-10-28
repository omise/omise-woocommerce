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

			$this->supports           = array( 'products', 'refunds' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_api_' . $this->id . '_callback', array( $this, 'callback' ) );
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
		public function payment_fields() {
			Omise_Util::render_view( 'templates/payment/form-truemoney.php', array() );
		}

		/**
		 * @inheritdoc
		 */
		public function charge( $order_id, $order ) {
			$phone_number = isset( $_POST['omise_phone_number_default'] ) && 1 == $_POST['omise_phone_number_default'] ? $order->get_billing_phone() : sanitize_text_field( $_POST['omise_phone_number'] );
			$total        = $order->get_total();
			$currency     = $order->get_order_currency();
			$return_uri   = add_query_arg(
				array( 'wc-api' => 'omise_truemoney_callback', 'order_id' => $order_id ), home_url()
			);
			$metadata     = array_merge(
				apply_filters( 'omise_charge_params_metadata', array(), $order ),
				array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
			);

			return OmiseCharge::create( array(
				'amount'      => Omise_Money::to_subunit( $total, $currency ),
				'currency'    => $currency,
				'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
				'source'      => array( 'type' => 'truemoney', 'phone_number' => $phone_number ),
				'return_uri'  => $return_uri,
				'metadata'    => $metadata
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
							__( 'Omise: The payment has been processing.<br/>Due to the TrueMoney provider, this might takes a few seconds or an hour. Please do a manual \'Sync Payment Status\' action from the Order Actions panel or check the payment status directly at Omise dashboard again later', 'omise' ),
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

		/**
		 * Process refund.
		 *
		 * @param  int    $order_id
		 * @param  float  $amount
		 * @param  string $reason
		 *
		 * @return boolean True|False based on success, or a WP_Error object.
		 *
		 * @see    WC_Payment_Gateway::process_refund( $order_id, $amount = null, $reason = '' )
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			if ( ! isset( $order_id ) || ! $order = $this->load_order( $order_id ) ) {
				return new WP_Error(
					'error',
					sprintf(
						wp_kses(
							__( 'We cannot process your refund.<br/>Note that nothing wrong by you, this might be from the store issue.<br/><br/>Please feel try to create a refund again or report our support team that you have found this problem', 'omise' ),
							array(
								'br' => array()
							)
						),
						$order_id
					)
				);
			}

			$currency = $order->get_order_currency();

			$order->add_order_note(
				sprintf( __( 'Omise: Refunding a payment with an amount %1$s %2$s', 'omise' ), $amount, $currency )
			);

			try {
				$charge = OmiseCharge::retrieve( $order->get_transaction_id() );
				$refund = $charge->refunds()->create( array(
					'amount' => Omise_Money::to_subunit( $amount, $currency )
				) );

				$order->add_order_note(
					sprintf(
						wp_kses(
							__( 'Omise: Refunded an amount %1$s %2$s.<br/>Refund id is %3$s', 'omise' ),
							array( 'br' => array() )
						),
						$amount,
						$currency,
						$refund['id']
					)
				);

				return true;
			} catch (Exception $e) {
				$order->add_order_note(
					sprintf(
						wp_kses(
							__( 'Omise: Refund failed.<br/>%s', 'omise' ),
							array( 'br' => array() )
						),
						$e->getMessage()
					)
				);

				return new WP_Error(
					'error',
					sprintf(
						wp_kses(
							__( 'Omise: Refund failed.<br/>%s', 'omise' ),
							array( 'br' => array() )
						),
						$e->getMessage()
					)
				);
			}

			return false;
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
