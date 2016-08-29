<?php
defined( 'ABSPATH' ) or die ( "No direct script access allowed." );

function register_omise_wc_gateway_plugin() {
	// prevent running directly without wooCommerce
	if ( ! class_exists ( 'WC_Payment_Gateway' ) )
		return;

	if ( ! class_exists ( 'WC_Gateway_Omise' ) ) {

		class WC_Gateway_Omise extends WC_Payment_Gateway {
			var $gateway_name = "Omise";

			public function __construct() {
				$this->id               = 'omise';
				$this->method_title     = "Omise";
				$this->has_fields       = true;

				// call base functions required for WooCommerce gateway
				$this->init_form_fields();
				$this->init_settings();

				$this->title             = $this->settings['title'];
				$this->sandbox           = isset( $this->settings['sandbox'] ) && $this->settings['sandbox'] == 'yes';
				$this->payment_action    = $this->settings['payment_action'];
				$this->omise_3ds         = isset( $this->settings['omise_3ds'] ) && $this->settings['omise_3ds'] == 'yes';
				$this->test_private_key  = $this->settings['test_private_key'];
				$this->test_public_key   = $this->settings['test_public_key'];
				$this->live_private_key  = $this->settings['live_private_key'];
				$this->live_public_key   = $this->settings['live_public_key'];
				$this->public_key        = $this->sandbox ? $this->test_public_key : $this->live_public_key;
				$this->private_key       = $this->sandbox ? $this->test_private_key : $this->live_private_key;
				$this->accept_amex       = isset( $this->settings['accept_amex'] ) && $this->settings['accept_amex'] == 'yes';
				$this->accept_diners     = isset( $this->settings['accept_diners'] ) && $this->settings['accept_diners'] == 'yes';
				$this->accept_jcb        = isset( $this->settings['accept_jcb'] ) && $this->settings['accept_jcb'] == 'yes';
				$this->accept_mastercard = isset( $this->settings['accept_mastercard'] ) && $this->settings['accept_mastercard'] == 'yes';
				$this->accept_visa       = isset( $this->settings['accept_visa'] ) && $this->settings['accept_visa'] == 'yes';

				add_action( 'woocommerce_api_wc_gateway_' . $this->id, array( Omise_Hooks::get_instance(), 'charge_3ds_callback' ) );
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'omise_scripts' ) );
			}

			/**
			 * initialize settings fields
			 * @see WC_Settings_API::init_form_fields()
			 */
			function init_form_fields() {
				$this->form_fields = include( 'includes/omise-wc-setting.php' );
			}

			/**
			 * Settings on Admin page
			 * @see WC_Settings_API::admin_options()
			 */
			public function admin_options() {
				echo '<h3>' . Omise_Util::translate( 'Omise Payment Gateway', 'Header at setting page' ) . '</h3>';
				echo '<table class="form-table">';
				$this->generate_settings_html();
				echo '</table>';
			}

			/**
			 * Payment fields which will be rendered on checkout page
			 * @see WC_Payment_Gateway::payment_fields()
			 */
			function payment_fields() {
				if ( is_user_logged_in() ) {
					$viewData["user_logged_in"] = true;
					$current_user = wp_get_current_user();
					$omise_customer_id = $this->sandbox ? $current_user->test_omise_customer_id : $current_user->live_omise_customer_id;
					if ( ! empty( $omise_customer_id ) ) {
						$cards = Omise::get_customer_cards( $this->private_key, $omise_customer_id );
						$viewData["existingCards"] = $cards;
					}
				} else {
					$viewData["user_logged_in"] = false;
				}

				Omise_Util::render_view( 'includes/templates/omise-payment-form.php', $viewData );
			}

			/**
			 * Process payment
			 *
			 * @see WC_Payment_Gateway::process_payment()
			 */
			public function process_payment( $order_id ) {
				Omise_Hooks::set_omise_user_agent();

				$order   = wc_get_order( $order_id );
				$token   = isset( $_POST['omise_token'] ) ? wc_clean( $_POST['omise_token'] ) : '';
				$card_id = isset( $_POST['card_id'] ) ? wc_clean( $_POST['card_id'] ) : '';
				try {
					$order->add_order_note( Omise_Util::translate( 'Starting to process payment with Omise' ) );

					if ( empty( $token ) && empty( $card_id ) ) {
						throw new Exception( Omise_Util::translate( 'Please select a card or enter new payment information.' ) );
					}

					$user              = $order->get_user();
					$omise_customer_id = $this->sandbox ? $user->test_omise_customer_id : $user->live_omise_customer_id;

					if ( isset( $_POST['omise_save_customer_card'] ) && empty( $card_id ) ) {
						if ( empty( $token ) ) {
							throw new Exception( Omise_Util::translate( 'Omise card token is required.' ) );
						}

						if ( ! empty( $omise_customer_id ) ) {
							// attach a new card to customer
							$omise_customer = Omise::create_card( $this->private_key, $omise_customer_id, $token );

							if ( $omise_customer->object == "error" ) {
								throw new Exception( $omise_customer->message );
							}

							$card_id = $omise_customer->cards->data[$omise_customer->cards->total - 1]->id;
						} else {
							$description   = "WooCommerce customer " . $user->id;
							$customer_data = array(
								"description" => $description,
								"card"        => $token
							);

							$omise_customer = Omise::create_customer( $this->private_key, $customer_data );

							if ( $omise_customer->object == "error" ) {
								throw new Exception( $omise_customer->message );
							}

							$omise_customer_id = $omise_customer->id;
							if ( $this->sandbox ) {
								update_user_meta( $user->ID, 'test_omise_customer_id', $omise_customer_id );
							} else {
								update_user_meta( $user->ID, 'live_omise_customer_id', $omise_customer_id );
							}

							if ( 0 == sizeof( $omise_customer->cards->data ) ) {
								throw new Exception( Omise_Util::translate( 'Something wrong with Omise gateway. No card available for creating a charge.' ) );
							}
							$card    = $omise_customer->cards->data [0]; //use the latest card
							$card_id = $card->id;
						}
					}

					$success        = false;
					$order_currency = $order->get_order_currency();
					if ( 'THB' === strtoupper( $order_currency ) )
						$amount = $order->get_total() * 100;
					else
						$amount = $order->get_total();

					$data = array(
						"amount"      => $amount,
						"currency"    => $order_currency,
						"description" => "WooCommerce Order id " . $order_id,
						"return_uri"  => add_query_arg( 'order_id', $order_id, site_url() . "?wc-api=wc_gateway_omise" )
					);

					if ( ! empty( $card_id ) && ! empty( $omise_customer_id ) ) {
						// create charge with a specific card of customer
						$data["customer"] = $omise_customer_id;
						$data["card"]     = $card_id;
					} else if ( ! empty( $token ) ) {
						$data["card"] = $token;
					} else {
						throw new Exception ( Omise_Util::translate( 'Please select a card or enter new payment information.' ) );
					}

					// Set capture status (otherwise, use API's default behaviour)
					if ( 'AUTO_CAPTURE' === strtoupper( $this->payment_action ) ) {
						$data['capture'] = true;
					} else if ( 'MANUAL_CAPTURE' === strtoupper( $this->payment_action ) ) {
						$data['capture'] = false;
					}

					$charge = OmiseCharge::create( $data, '', $this->private_key );
					if ( ! Omise_Charge::is_charge_object( $charge ) )
						throw new Exception( Omise_Util::translate( 'Charge was failed, please contact our support' ) );

					// Register new post
					$this->register_omise_charge_post( $charge, $order, $order_id );

					if ( Omise_Charge::is_failed( $charge ) )
						throw new Exception( Omise_Charge::get_error_message( $charge ) );

					if ( $this->omise_3ds ) {
						$order->add_order_note( Omise_Util::translate( 'Processing payment with Omise 3D-Secure' ) );
						return array (
							'result'   => 'success',
							'redirect' => $charge['authorize_uri'],
						);
					} else {
						switch ( strtoupper( $this->payment_action ) ) {
							case 'MANUAL_CAPTURE':
								$success = Omise_Charge::is_authorized( $charge );
								if ( $success ) {
									$order->add_order_note( Omise_Util::translate( 'Authorize with Omise successful' ) );
								}

								break;

							case 'AUTO_CAPTURE':
								$success = Omise_Charge::is_paid( $charge );
								if ( $success ) {
									$order->payment_complete();
									$order->add_order_note( Omise_Util::translate( 'Payment with Omise successful' ) );
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

						if ( ! $success )
							throw new Exception( Omise_Util::translate( 'This charge cannot authorize or capture, please contact our support.' ) );

						// Remove cart
						WC()->cart->empty_cart();
						return array (
							'result'   => 'success',
							'redirect' => $this->get_return_url( $order )
						);
					}
				} catch( Exception $e ) {
					$error_message = $e->getMessage();
					wc_add_notice( Omise_Util::translate( 'Payment error' ) . ': ' . $error_message, 'error' );
					$order->add_order_note( Omise_Util::translate( 'Payment with Omise error' ) . ': ' . $error_message );
					return array(
						'result'   => 'fail',
						'redirect' => ''
					);
				}
			}

			private function register_omise_charge_post( $charge, $order, $order_id ) {
				$post_id = wp_insert_post(
					array(
						'post_title'  => 'Omise Charge Id ' . $charge['id'],
						'post_type'   => 'omise_charge_items',
						'post_status' => 'publish'
					)
				);

				add_post_meta( $post_id, '_omise_charge_id', $charge['id'] );
				add_post_meta( $post_id, '_wc_order_id', $order_id );
				add_post_meta( $post_id, '_wc_confirmed_url', $this->get_return_url( $order ) );
			}

			/**
			 * Get icons of all supported card types
			 * @see WC_Payment_Gateway::get_icon()
			 */
			public function get_icon() {
				$icon = '';

				if ( Omise_Card_Image::is_visa_enabled( $this->settings ) ) {
					$icon .= Omise_Card_Image::get_visa_image();
				}

				if ( Omise_Card_Image::is_mastercard_enabled( $this->settings ) ) {
					$icon .= Omise_Card_Image::get_mastercard_image();
				}

				if ( Omise_Card_Image::is_jcb_enabled( $this->settings ) ) {
					$icon .= Omise_Card_Image::get_jcb_image();
				}

				if ( Omise_Card_Image::is_diners_enabled( $this->settings ) ) {
					$icon .= Omise_Card_Image::get_diners_image();
				}

				if ( Omise_Card_Image::is_amex_enabled( $this->settings ) ) {
					$icon .= Omise_Card_Image::get_amex_image();
				}

				return empty( $icon ) ? '' : apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
			}

			/**
			 * Register all javascripts
			 */
			public function omise_scripts() {
				if ( ! is_checkout() || ! $this->is_available() ) {
					return;
				}

				wp_enqueue_style( 'omise-css', plugins_url( '/assets/css/omise-css.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );

				wp_enqueue_script( 'omise-js', 'https://cdn.omise.co/omise.js', array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'omise-util', plugins_url( '/assets/javascripts/omise-util.js', __FILE__ ), array( 'omise-js' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
				wp_enqueue_script( 'omise-payment-form-handler', plugins_url( '/assets/javascripts/omise-payment-form-handler.js', __FILE__ ), array( 'omise-js', 'omise-util' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

				wp_localize_script( 'omise-payment-form-handler', 'omise_params', array(
					'key'       => $this->public_key,
					'vault_url' => OMISE_VAULT_HOST
				) );
			}
		}
	}

	function add_omise_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Omise';
		return $methods;
	}

	function add_omise_capture_action( $order_actions ) {
		$order_actions['omise_charge_capture'] = __( "Capture charge (via Omise)" );
		return $order_actions;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_omise_gateway' );
	add_filter( 'woocommerce_order_actions', 'add_omise_capture_action' );

	load_plugin_textdomain( OMISE_WOOCOMMERCE_TEXT_DOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}

function register_omise_wc_gateway_post_type() {
	register_post_type('omise_charge_items', array(
		'label' => 	'Omise Charge Items',
		'labels' =>	array(
			'name' => 'Omise Charge Items',
			'singular_name' => 'Omise Charge Item'
		),
		'supports'	=> array('title','custom-fields')
	));
}
?>
