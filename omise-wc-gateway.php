<?php
defined ( 'ABSPATH' ) or die ( "No direct script access allowed." );

function register_omise_wc_gateway_plugin() {
	// prevent running directly without wooCommerce
	if (! class_exists ( 'WC_Payment_Gateway' ))
		return;
	
	if (! class_exists ( 'WC_Gateway_Omise' )) {
		
		class WC_Gateway_Omise extends WC_Payment_Gateway {
			var $gateway_name = "Omise";

			public function __construct() {
				$this->id = 'omise';
				$this->method_title = "Omise";
				$this->has_fields = true;
				
				// call base functions required for wooCommerce gateway
				$this->init_form_fields ();
				$this->init_settings ();
				
				$this->title = $this->settings ['title'];
				$this->description = $this->settings ['description'];
				$this->sandbox = $this->settings ['sandbox'];
				$this->test_private_key = $this->settings ['test_private_key'];
				$this->test_public_key = $this->settings ['test_public_key'];
				$this->live_private_key = $this->settings ['live_private_key'];
				$this->live_public_key = $this->settings ['live_public_key'];
				$this->public_key = $this->sandbox ? $this->test_public_key : $this->live_public_key;
				$this->private_key = $this->sandbox ? $this->test_private_key : $this->live_private_key;
				
				add_action ( 'woocommerce_update_options_payment_gateways_' . $this->id, array (
						&$this,
						'process_admin_options' 
				));
				
				add_action ( 'wp_enqueue_scripts', array (
						$this,
						'omise_scripts' 
				));
			}

			function init_form_fields() {
				$this->form_fields = array (
						'enabled' => array (
								'title' => __ ( 'Enable/Disable', $this->gateway_name ),
								'type' => 'checkbox',
								'label' => __ ( 'Enable Omise Payment Module.', $this->gateway_name ),
								'default' => 'no' 
						),
						'sandbox' => array (
								'title' => __ ( 'Sandbox', $this->gateway_name ),
								'type' => 'checkbox',
								'label' => __ ( 'Sandbox mode means everything is in TEST mode', $this->gateway_name ),
								'default' => 'yes' 
						),
						'title' => array (
								'title' => __ ( 'Title:', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'This controls the title which the user sees during checkout.', $this->gateway_name ),
								'default' => __ ( 'Omise payment gateway', $this->gateway_name ) 
						),
						'description' => array (
								'title' => __ ( 'Description:', $this->gateway_name ),
								'type' => 'textarea',
								'description' => __ ( 'This controls the description which the user sees during checkout.', $this->gateway_name ),
								'default' => __ ( 'Omise payment gateway.', $this->gateway_name ) 
						),
						'test_private_key' => array (
								'title' => __ ( 'Private key for test', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Test" mode private key which can be found in Omise Dashboard"' ) 
						),
						'test_public_key' => array (
								'title' => __ ( 'Public key for test', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Test" mode public key which can be found in Omise Dashboard"' ) 
						),
						'live_private_key' => array (
								'title' => __ ( 'Private key for live', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Live" mode private key which can be found in Omise Dashboard"' ) 
						),
						'live_public_key' => array (
								'title' => __ ( 'Public key for live', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Live" mode public key which can be found in Omise Dashboard"' ) 
						) 
				);
			}

			public function admin_options() {
				echo '<h3>' . __ ( 'Omise Payment Gateway', $this->gateway_name ) . '</h3>';
				echo '<p>' . __ ( 'Omise payment gateway. The first PCI certified payment gateway in Thailand' ) . '</p>';
				echo '<table class="form-table">';
				$this->generate_settings_html ();
				echo '</table>';
			}
			
			/**
			 * Payment fields which will be rendered on checkout page
			 * @see WC_Payment_Gateway::payment_fields()
			 */
			function payment_fields() {
				if (is_user_logged_in ()) {
					$viewData ["user_logged_in"] = true;
					$current_user = wp_get_current_user ();
					$omise_customer_id = $current_user->omise_customer_id;
					if (! empty ( $omise_customer_id )) {
						$cards = Omise::get_customer_cards ( $this->private_key, $omise_customer_id );
						$viewData ["existingCards"] = $cards;
					}
				}
				
				Omise_Util::render_view ( 'includes/templates/omise-payment-form.php', $viewData );
			}
			
			/**
			 * Process payment
			 * 
			 * @see WC_Payment_Gateway::process_payment()
			 */
			public function process_payment($order_id) {
				if (! isset( $_POST['omise_nonce'] ) || 
						! wp_verify_nonce( $_POST['omise_nonce'], 'omise_checkout' )) {
				
					throw new Exception ( "Nonce verified failure." );
					exit;
				}
				
				$order = wc_get_order ( $order_id );
				$token = isset ( $_POST ['omise_token'] ) ? wc_clean ( $_POST ['omise_token'] ) : '';
				$card_id = isset ( $_POST ['card_id'] ) ? wc_clean ( $_POST ['card_id'] ) : '';
				$nonce = isset ( $_POST ['omise_nonce'] ) ? wc_clean ( $_POST ['omise_nonce'] ) : '';
				
				if (empty ( $token ) && empty ( $card_id )) {
					throw new Exception ( "Please select a card or create new card" );
					return;
				}
				
				$user = $order->get_user ();
				$omise_customer_id = $user->omise_customer_id;
				
				if (isset ( $_POST ['omise_save_customer_card'] )) {
					if (! empty ( $omise_customer_id )) {
						// attach a new card to customer
						$omise_customer = Omise::create_card ( $this->private_key, $omise_customer_id, $token );
						
						if($omise_customer->object=="error"){
							throw new Exception($omise_customer->message);
						}
						
						$card_id = $omise_customer->cards->data [$omise_customer->cards->total - 1]->id;
					} else {
						$first_name = $user->first_name;
						$last_name = $user->last_name;
						$email = $user->user_email;
						$description = "WooCommerce customer " . $user->id;
						$customer_data = array (
								"first_name" => $first_name,
								"last_name" => $last_name,
								"email" => $email,
								"description" => $description,
								"card" => $token 
						);
						
						$omise_customer = Omise::create_customer ( $this->private_key, $customer_data );
						
						if($omise_customer->object=="error"){
							throw new Exception($omise_customer->message);
						}
						
						$omise_customer_id = $omise_customer->id;
						update_user_meta ( $user->ID, 'omise_customer_id', $omise_customer_id );
						
						if (0 == sizeof ( $omise_customer->cards->data )) {
							throw new Exception ( "Something wrong with Omise gateway. No card available for creating a charge." );
						}
						$card = $omise_customer->cards->data [0]; //use the latest card
						$card_id = $card->id;
					}
				}
				
				$success = false;
				$data = array (
					"amount" => $order->get_total () * 100,
					"currency" => $order->get_order_currency (),
					"description" => "WooCommerce Order id " . $order_id,
					"ip" => Omise_Util::get_client_ip()
				);
				
				if (! empty ( $card_id ) && ! empty ( $omise_customer_id )) {
					// create charge with a specific card of customer
					$data["customer"] =  $omise_customer_id;
					$data["card"] = $card_id;
				} else if (! empty ( $token )) {
					$data["card"] = $token;
				} else {
					throw new Exception ( "Please select a card or create new card" );
				}
				
				$result = Omise::create_charge ( $this->private_key, $data );
				$success = isset ( $result->id );
				
				if ($success) {
					$order->payment_complete ();
					$order->add_order_note ( 'Payment with Omise successful' );
					return array (
							'result' => 'success',
							'redirect' => $this->get_return_url ( $order ) 
					);
				} else {
					wc_add_notice( __('Payment error:', 'woothemes') . $result->message, 'error' );
					return;
				}
			}

			public function get_icon() {
				$icon = '<img src="' . WC_HTTPS::force_https_url ( WC ()->plugin_url () . '/assets/images/icons/credit-cards/visa.png' ) . '" alt="Visa" />';
				$icon .= '<img src="' . WC_HTTPS::force_https_url ( WC ()->plugin_url () . '/assets/images/icons/credit-cards/mastercard.png' ) . '" alt="Mastercard" />';
				
				return apply_filters ( 'woocommerce_gateway_icon', $icon, $this->id );
			}

			public function omise_scripts() {
				if (! is_checkout () || ! $this->is_available ()) {
					return;
				}
				
				wp_enqueue_script ( 'omise-js', 'https://cdn.omise.co/omise.js', array (
						'jquery' 
				), WC_VERSION, true );
				wp_enqueue_script ( 'omise-util', plugins_url ( '/assets/javascripts/omise-util.js', __FILE__ ), array (
				'omise-js'), WC_VERSION, true );
				wp_enqueue_script ( 'omise-payment-form-handler', plugins_url ( '/assets/javascripts/omise-payment-form-handler.js', __FILE__ ), array (
						'omise-js', 'omise-util'), WC_VERSION, true );
				wp_localize_script ( 'omise-payment-form-handler', 'omise_params', array (
						'key' => $this->public_key,
						'vault_url' => OMISE_VAULT_HOST
				) );
			}
		}
	}

	function add_omise_gateway($methods) {
		$methods [] = 'WC_Gateway_Omise';
		return $methods;
	}
	
	add_filter ( 'woocommerce_payment_gateways', 'add_omise_gateway' );
}

?>
