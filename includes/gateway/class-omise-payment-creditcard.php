<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_creditcard() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'Omise_Payment_Creditcard' ) ) {
		return;
	}

	class Omise_Payment_Creditcard extends Omise_Payment {
		public function __construct() {
			parent::__construct();

			$this->id                 = 'omise';
			$this->has_fields         = true;
			$this->method_title       = __( 'Omise Credit / Debit Card', 'omise' );
			$this->method_description = wp_kses(
				__( 'Accept payment through <strong>Credit / Debit Card</strong> via Omise payment gateway.', 'omise' ),
				array(
					'strong' => array()
				)
			);
			$this->supports           = array( 'products', 'refunds' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title          = $this->get_option( 'title' );
			$this->description    = $this->get_option( 'description' );
			$this->omise_3ds      = $this->get_option( 'omise_3ds', false ) == 'yes';
			$this->payment_action = $this->get_option( 'payment_action' );

			add_action( 'woocommerce_api_' . $this->id . '_callback', array( $this, 'callback' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'omise_assets' ) );
			add_action( 'woocommerce_order_action_' . $this->id . '_charge_capture', array( $this, 'capture' ) );
			add_action( 'woocommerce_order_action_' . $this->id . '_sync_payment', array( $this, 'sync_payment' ) );

			/** @deprecated 3.0 */
			add_action( 'woocommerce_api_wc_gateway_' . $this->id, array( $this, 'callback' ) );
		}

		/**
		 * @see WC_Settings_API::init_form_fields()
		 * @see woocommerce/includes/abstracts/abstract-wc-settings-api.php
		 */
		function init_form_fields() {
			$this->form_fields = array_merge(
				array(
					'enabled' => array(
						'title'   => __( 'Enable/Disable', 'omise' ),
						'type'    => 'checkbox',
						'label'   => __( 'Enable Omise Credit / Debit Card Payment', 'omise' ),
						'default' => 'no'
					),

					'title' => array(
						'title'       => __( 'Title', 'omise' ),
						'type'        => 'text',
						'description' => __( 'This controls the title which the user sees during checkout.', 'omise' ),
						'default'     => __( 'Credit / Debit Card', 'omise' )
					),

					'description' => array(
						'title'       => __( 'Description', 'omise' ),
						'type'        => 'textarea',
						'description' => __( 'This controls the description which the user sees during checkout.', 'omise' )
					),
				),
				array(
					'advanced' => array(
						'title'       => __( 'Advance Settings', 'omise' ),
						'type'        => 'title'
					),
					'payment_action' => array(
						'title'       => __( 'Payment action', 'omise' ),
						'type'        => 'select',
						'description' => __( 'Capture automatically during the checkout process or manually after order has been placed', 'omise' ),
						'default'     => 'auto_capture',
						'class'       => 'wc-enhanced-select',
						'options'     => array(
							'auto_capture'   => __( 'Auto Capture', 'omise' ),
							'manual_capture' => __( 'Manual Capture', 'omise' )
						),
						'desc_tip'    => true
					),
					'omise_3ds' => array(
						'title'       => __( '3-D Secure payment', 'omise' ),
						'type'        => 'checkbox',
						'label'       => __( 'Enabling 3-D Secure payment means that buyer will be redirected to the 3-D Secure authorization page during the checkout process.', 'omise' ),
						'description' => wp_kses(
							__( 'Please be informed that you must contact Omise support team (support@omise.co) before enable or disable this option.<br/> (Japan-based accounts are not eligible for the service.)', 'omise' ),
							array( 'br' => array() )
						),
						'default'     => 'no'
					),
					'accept_visa' => array(
						'title'       => __( 'Supported card icons', 'omise' ),
						'type'        => 'checkbox',
						'label'       => Omise_Card_Image::get_visa_image(),
						'css'         => Omise_Card_Image::get_css(),
						'default'     => Omise_Card_Image::get_visa_default_display()
					),
					'accept_mastercard' => array(
						'type'        => 'checkbox',
						'label'       => Omise_Card_Image::get_mastercard_image(),
						'css'         => Omise_Card_Image::get_css(),
						'default'     => Omise_Card_Image::get_mastercard_default_display()
					),
					'accept_jcb' => array(
						'type'        => 'checkbox',
						'label'       => Omise_Card_Image::get_jcb_image(),
						'css'         => Omise_Card_Image::get_css(),
						'default'     => Omise_Card_Image::get_jcb_default_display()
					),
					'accept_diners' => array(
						'type'        => 'checkbox',
						'label'       => Omise_Card_Image::get_diners_image(),
						'css'         => Omise_Card_Image::get_css(),
						'default'     => Omise_Card_Image::get_diners_default_display()
					),
					'accept_amex' => array(
						'type'        => 'checkbox',
						'label'       => Omise_Card_Image::get_amex_image(),
						'css'         => Omise_Card_Image::get_css(),
						'default'     => Omise_Card_Image::get_amex_default_display(),
						'description' => wp_kses(
							__( 'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Omise payment gateway.', 'omise' ),
							array( 'br' => array() )
						)
					)
				)
			);
		}

		/**
		 * @see WC_Payment_Gateway::payment_fields()
		 * @see woocommerce/includes/abstracts/abstract-wc-payment-gateway.php
		 */
		public function payment_fields() {
			parent::payment_fields();

			if ( is_user_logged_in() ) {
				$viewData['user_logged_in'] = true;

				$current_user      = wp_get_current_user();
				$omise_customer_id = $this->is_test() ? $current_user->test_omise_customer_id : $current_user->live_omise_customer_id;
				if ( ! empty( $omise_customer_id ) ) {
					try {
						$customer                  = OmiseCustomer::retrieve( $omise_customer_id, '', $this->secret_key() );
						$viewData['existingCards'] = $customer->cards( array( 'order' => 'reverse_chronological' ) );
					} catch (Exception $e) {
						// nothing
					}
				}
			} else {
				$viewData['user_logged_in'] = false;
			}

			Omise_Util::render_view( 'templates/payment/form.php', $viewData );
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
				wc_add_notice(
					sprintf(
						wp_kses(
							__( 'We cannot process your payment.<br/>Note that nothing wrong by you, this might be from our store issue.<br/><br/>Please feel free to try submit your order again or report our support team that you have found this problem (Your temporary order id is \'%s\')', 'omise' ),
							array(
								'br' => array()
							)
						),
						$order_id
					),
					'error'
				);
				return;
			}

			$order->add_order_note( __( 'Omise: Processing a payment with Credit Card solution..', 'omise' ) );

			try {
				$token   = isset( $_POST['omise_token'] ) ? wc_clean( $_POST['omise_token'] ) : '';
				$card_id = isset( $_POST['card_id'] ) ? wc_clean( $_POST['card_id'] ) : '';

				if ( empty( $token ) && empty( $card_id ) ) {
					throw new Exception( __( 'Please select an existing card or enter a new card information.', 'omise' ) );
				}

				$user              = $order->get_user();
				$omise_customer_id = $this->is_test() ? $user->test_omise_customer_id : $user->live_omise_customer_id;

				if ( isset( $_POST['omise_save_customer_card'] ) && empty( $card_id ) ) {
					if ( empty( $token ) ) {
						throw new Exception( __( 'Cannot process with a card that you\'re using. Please make sure that the card info is correct or contact our support team if you have any questions.', 'omise' ) );
					}

					if ( ! empty( $omise_customer_id ) ) {
						try {
							// attach a new card to customer
							$customer = OmiseCustomer::retrieve( $omise_customer_id, '', $this->secret_key() );
							$customer->update( array(
								'card' => $token
							) );

							$cards = $customer->cards( array(
								'limit' => 1,
								'order' => 'reverse_chronological'
							) );

							$card_id = $cards['data'][0]['id'];
						} catch (Exception $e) {
							throw new Exception( $e->getMessage() );
						}
					} else {
						$description   = "WooCommerce customer " . $user->ID;
						$customer_data = array(
							"description" => $description,
							"card"        => $token
						);

						$omise_customer = OmiseCustomer::create( $customer_data, '', $this->secret_key() );

						if ( $omise_customer['object'] == "error" ) {
							throw new Exception( $omise_customer['message'] );
						}

						$omise_customer_id = $omise_customer['id'];
						if ( $this->is_test() ) {
							update_user_meta( $user->ID, 'test_omise_customer_id', $omise_customer_id );
						} else {
							update_user_meta( $user->ID, 'live_omise_customer_id', $omise_customer_id );
						}

						if ( 0 == sizeof( $omise_customer['cards']['data'] ) ) {
							throw new Exception(
								sprintf(
									wp_kses(
										__( 'Note that nothing wrong by you, this might be from our store issue.<br/><br/>Please feel free to try submit your order again or report our support team that you have found this problem (Your temporary order id is \'%s\')', 'omise' ),
										array(
											'br' => array()
										)
									),
									$order_id
								)
							);
						}

						$cards   = $omise_customer->cards( array( 'order' => 'reverse_chronological' ) );
						$card_id = $cards['data'][0]['id']; //use the latest card
					}
				}

				$success = false;
				$data    = array(
					'amount'      => $this->format_amount_subunit( $order->get_total(), $order->get_order_currency() ),
					'currency'    => $order->get_order_currency(),
					'description' => 'WooCommerce Order id ' . $order_id,
					'return_uri'  => add_query_arg( 'order_id', $order_id, site_url() . '?wc-api=omise_callback' )
				);

				if ( ! empty( $omise_customer_id ) && ! empty( $card_id ) ) {
					$data['customer'] = $omise_customer_id;
					$data['card']     = $card_id;
				} else {
					$data['card'] = $token;
				}

				// Set capture status (otherwise, use API's default behaviour)
				if ( 'AUTO_CAPTURE' === strtoupper( $this->payment_action ) ) {
					$data['capture'] = true;
				} else if ( 'MANUAL_CAPTURE' === strtoupper( $this->payment_action ) ) {
					$data['capture'] = false;
				}

				/** backward compatible with WooCommerce v2.x series **/
				$data['metadata'] = array(
					'order_id' => version_compare( WC()->version, '3.0.0', '>=' ) ? $order->get_id() : $order->id
				);

				$charge = OmiseCharge::create( $data, '', $this->secret_key() );

				$order->add_order_note( sprintf( __( 'Omise: Charge (ID: %s) has been created', 'omise' ), $charge['id'] ) );

				$this->attach_charge_id_to_order( $charge['id'] );

				if ( Omise_Charge::is_failed( $charge ) ) {
					throw new Exception( Omise_Charge::get_error_message( $charge ) );
				}

				/** backward compatible with WooCommerce v2.x series **/
				if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
					$order->set_transaction_id( $charge['id'] );
					$order->save();
				} else {
					update_post_meta( $order->id, '_transaction_id', $charge['id'] );
				}

				if ( $this->omise_3ds ) {
					$order->add_order_note(
						sprintf(
							__( 'Omise: Processing with a 3-D Secure payment, redirecting buyer out to %s', 'omise' ),
							esc_url( $charge['authorize_uri'] )
						)
					);

					return array(
						'result'   => 'success',
						'redirect' => $charge['authorize_uri'],
					);
				} else {
					switch ( strtoupper( $this->payment_action ) ) {
						case 'MANUAL_CAPTURE':
							$success = Omise_Charge::is_authorized( $charge );
							if ( $success ) {
								$order->add_order_note(
									sprintf(
										wp_kses(
											__( 'Omise: Payment processing.<br/>An amount %1$s %2$s has been authorized', 'omise' ),
											array( 'br' => array() )
										),
										$order->get_total(),
										$order->get_order_currency()
									)
								);
							}

							break;

						case 'AUTO_CAPTURE':
							$success = Omise_Charge::is_paid( $charge );
							if ( $success ) {
								$order->payment_complete();
								$order->add_order_note(
									sprintf(
										wp_kses(
											__( 'Omise: Payment successful.<br/>An amount %1$s %2$s has been paid', 'omise' ),
											array( 'br' => array() )
										),
										$order->get_total(),
										$order->get_order_currency()
									)
								);
							}

							break;

						default:
							// Default behaviour is, check if it paid first.
							$success = Omise_Charge::is_paid( $charge );

							// Then, check is authorized after if the first condition is false.
							if ( ! $success )
								$success = Omise_Charge::is_authorized( $charge );

							break;
					}

					if ( ! $success ) {
						throw new Exception( __( 'Note that your payment might already has been processed. Please contact our support team if you have any questions.', 'omise' ) );
					}

					// Remove cart
					WC()->cart->empty_cart();
					return array (
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order )
					);
				}
			} catch( Exception $e ) {
				wc_add_notice(
					sprintf(
						wp_kses(
							__( 'Seems we cannot process your payment properly:<br/>%s', 'omise' ),
							array( 'br' => array() )
						),
						$e->getMessage()
					),
					'error'
				);

				$order->add_order_note(
					sprintf(
						__( 'Omise: Payment failed, %s', 'omise' ),
						$e->getMessage()
					)
				);

				return;
			}
		}

		/**
		 * Process refund.
		 *
		 * @param  int $order_id
		 * @param  float $amount
		 * @param  string $reason
		 *
		 * @return boolean True or false based on success, or a WP_Error object.
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

			$order->add_order_note(
				sprintf(
					__( 'Omise: Refunding a payment with an amount %1$s %2$s', 'omise' ),
					$amount,
					$order->get_order_currency()
				)
			);

			try {
				$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order(), '', $this->secret_key() );
				$refund = $charge->refunds()->create( array(
					'amount' => $this->format_amount_subunit( $amount, $order->get_order_currency() )
				) );

				if ( $refund['voided'] ) {
					$order->add_order_note(
						sprintf(
							wp_kses(
								__( 'Omise: Voided an amount %1$s %2$s.<br/>Refund id is %3$s', 'omise' ),
								array( 'br' => array() )
							),
							$amount,
							$order->get_order_currency(),
							$refund['id']
						)
					);
				} else {
					$order->add_order_note(
						sprintf(
							wp_kses(
								__( 'Omise: Refunded an amount %1$s %2$s.<br/>Refund id is %3$s', 'omise' ),
								array( 'br' => array() )
							),
							$amount,
							$order->get_order_currency(),
							$refund['id']
						)
					);
				}

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

		public function callback() {
			if ( ! isset( $_GET['order_id'] ) || ! $order = $this->load_order( $_GET['order_id'] ) ) {
				wc_add_notice(
					wp_kses(
						__( 'We cannot validate your payment result:<br/>Note that your payment might already has been processed. Please contact our support team if you have any questions.', 'omise' ),
						array( 'br' => array() )
					),
					'error'
				);

				header( 'Location: ' . WC()->cart->get_checkout_url() );
				die();
			}

			$order->add_order_note( __( 'Omise: Validating the payment result..', 'omise' ) );

			try {
				$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order(), '', $this->secret_key() );

				switch ( strtoupper( $this->payment_action ) ) {
					case 'MANUAL_CAPTURE':
						$success = Omise_Charge::is_authorized( $charge );
						if ( $success ) {
							$order->add_order_note(
								sprintf(
									wp_kses(
										__( 'Omise: Payment processing.<br/>An amount %1$s %2$s has been authorized', 'omise' ),
										array( 'br' => array() )
									),
									$order->get_total(),
									$order->get_order_currency()
								)
							);
						}

						break;

					case 'AUTO_CAPTURE':
						$success = Omise_Charge::is_paid( $charge );
						if ( $success ) {
							$order->add_order_note(
								sprintf(
									wp_kses(
										__( 'Omise: Payment successful.<br/>An amount %1$s %2$s has been paid', 'omise' ),
										array( 'br' => array() )
									),
									$order->get_total(),
									$order->get_order_currency()
								)
							);
							$order->payment_complete();
						}

						break;

					default:
						// Default behaviour is, check if it paid first.
						$success = Omise_Charge::is_paid( $charge );

						// Then, check is authorized after if the first condition is false.
						if ( ! $success )
							$success = Omise_Charge::is_authorized( $charge );

						break;
				}

				if ( ! $success ) {
					throw new Exception( Omise_Charge::get_error_message( $charge ) );
				}

				// Remove cart
				WC()->cart->empty_cart();
				header( 'Location: ' . $this->get_return_url( $order ) );
				die();
			} catch ( Exception $e ) {
				wc_add_notice(
					sprintf(
						wp_kses(
							__( 'Seems we cannot process your payment properly:<br/>%s', 'omise' ),
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

				header( 'Location: ' . WC()->cart->get_checkout_url() );
				die();
			}

			wp_die( 'Access denied', 'Access Denied', array( 'response' => 401 ) );
			die();
		}

		/**
		 * Get icons of all supported card types
		 *
		 * @see WC_Payment_Gateway::get_icon()
		 */
		public function get_icon() {
			$icon = '';

			// TODO: Refactor 'Omise_Card_Image' class that we don't need to pass
			//       these options to check outside this class.
			$card_icons['accept_amex']       = $this->get_option( 'accept_amex' );
			$card_icons['accept_diners']     = $this->get_option( 'accept_diners' );
			$card_icons['accept_jcb']        = $this->get_option( 'accept_jcb' );
			$card_icons['accept_mastercard'] = $this->get_option( 'accept_mastercard' );
			$card_icons['accept_visa']       = $this->get_option( 'accept_visa' );

			if ( Omise_Card_Image::is_visa_enabled( $card_icons ) ) {
				$icon .= Omise_Card_Image::get_visa_image();
			}

			if ( Omise_Card_Image::is_mastercard_enabled( $card_icons ) ) {
				$icon .= Omise_Card_Image::get_mastercard_image();
			}

			if ( Omise_Card_Image::is_jcb_enabled( $card_icons ) ) {
				$icon .= Omise_Card_Image::get_jcb_image();
			}

			if ( Omise_Card_Image::is_diners_enabled( $card_icons ) ) {
				$icon .= Omise_Card_Image::get_diners_image();
			}

			if ( Omise_Card_Image::is_amex_enabled( $card_icons ) ) {
				$icon .= Omise_Card_Image::get_amex_image();
			}

			return empty( $icon ) ? '' : apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
		}

		/**
		 * Register all required javascripts
		 */
		public function omise_assets() {
			if ( ! is_checkout() || ! $this->is_available() ) {
				return;
			}

			wp_enqueue_style( 'omise-css', plugins_url( '../../assets/css/omise-css.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );

			wp_enqueue_script( 'omise-js', 'https://cdn.omise.co/omise.js', array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
			wp_enqueue_script( 'omise-util', plugins_url( '../../assets/javascripts/omise-util.js', __FILE__ ), array( 'omise-js' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
			wp_enqueue_script( 'omise-payment-form-handler', plugins_url( '../../assets/javascripts/omise-payment-form-handler.js', __FILE__ ), array( 'omise-js', 'omise-util' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

			wp_localize_script( 'omise-payment-form-handler', 'omise_params', array(
				'key'       => $this->public_key()
			) );
		}
	}

	if ( ! function_exists( 'add_omise_creditcard' ) ) {
		/**
		 * @param  array $methods
		 *
		 * @return array
		 */
		function add_omise_creditcard( $methods ) {
			$methods[] = 'Omise_Payment_Creditcard';
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_omise_creditcard' );
	}
}

function register_omise_wc_gateway_post_type() {
	register_post_type(
		'omise_charge_items',
		array(
			'supports' => array('title','custom-fields'),
			'label'    => 'Omise Charge Items',
			'labels'   => array(
				'name'          => 'Omise Charge Items',
				'singular_name' => 'Omise Charge Item'
			)
		)
	);
}
