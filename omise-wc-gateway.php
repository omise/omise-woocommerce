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
				
				// call base functions required for WooCommerce gateway
				$this->init_form_fields ();
				$this->init_settings ();
				
				$this->title = $this->settings ['title'];
				$this->description = $this->settings ['description'];
				$this->sandbox = isset($this->settings ['sandbox']) && $this->settings ['sandbox'] == 'yes';
				$this->omise_3ds = isset($this->settings ['omise_3ds']) && $this->settings ['omise_3ds'] == 'yes';
				$this->test_private_key = $this->settings ['test_private_key'];
				$this->test_public_key = $this->settings ['test_public_key'];
				$this->live_private_key = $this->settings ['live_private_key'];
				$this->live_public_key = $this->settings ['live_public_key'];
				$this->public_key = $this->sandbox ? $this->test_public_key : $this->live_public_key;
				$this->private_key = $this->sandbox ? $this->test_private_key : $this->live_private_key;
				
				add_action( 'woocommerce_api_wc_gateway_' . $this->id, array( $this, 'omise_3ds_handler' ) );

				add_action ( 'woocommerce_update_options_payment_gateways_' . $this->id, array (
						&$this,
						'process_admin_options' 
				));
				
				add_action ( 'wp_enqueue_scripts', array (
						$this,
						'omise_scripts' 
				));

			}

			/**
			 * initialize settings fields
			 * @see WC_Settings_API::init_form_fields()
			 */
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
						'omise_3ds' => array (
								'title' => __ ( '3DS Support', $this->gateway_name ),
								'type' => 'checkbox',
								'label' => __ ( 'Enable the 3DS support', $this->gateway_name ),
								'default' => 'no'
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
								'title' => __ ( 'Secret key for test', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Test" mode secret key which can be found in Omise Dashboard"' ) 
						),
						'test_public_key' => array (
								'title' => __ ( 'Public key for test', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Test" mode public key which can be found in Omise Dashboard"' ) 
						),
						'live_private_key' => array (
								'title' => __ ( 'Secret key for live', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Live" mode secret key which can be found in Omise Dashboard"' ) 
						),
						'live_public_key' => array (
								'title' => __ ( 'Public key for live', $this->gateway_name ),
								'type' => 'text',
								'description' => __ ( 'The "Live" mode public key which can be found in Omise Dashboard"' ) 
						) 
				);
			}
			
			/**
			 * Settings on Admin page
			 * @see WC_Settings_API::admin_options()
			 */
			public function admin_options() {
				echo '<h3>' . __ ( 'Omise Payment Gateway', $this->gateway_name ) . '</h3>';
				echo '<p>' . __ ( 'Omise payment gateway. The first PCI 3.0 certified payment gateway in Thailand' ) . '</p>';
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
					$omise_customer_id = $this->sandbox ? $current_user->test_omise_customer_id : $current_user->live_omise_customer_id;
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
				$order = wc_get_order ( $order_id );
				$token = isset ( $_POST ['omise_token'] ) ? wc_clean ( $_POST ['omise_token'] ) : '';
				$card_id = isset ( $_POST ['card_id'] ) ? wc_clean ( $_POST ['card_id'] ) : '';
				try{
					if (empty ( $token ) && empty ( $card_id )) {
						throw new Exception ( "Please select a card or enter new payment information." );
					}

					$user = $order->get_user ();
					$omise_customer_id = $this->sandbox ? $user->test_omise_customer_id : $user->live_omise_customer_id;
					
					if (isset ( $_POST ['omise_save_customer_card'] ) && empty($card_id)) {
						if (empty($token)){
							throw new Exception ( "Omise card token is required." );
						}
						
						if (! empty ( $omise_customer_id )) {
							// attach a new card to customer
							$omise_customer = Omise::create_card ( $this->private_key, $omise_customer_id, $token );
							
							if($omise_customer->object=="error"){
								throw new Exception($omise_customer->message);
							}
							
							$card_id = $omise_customer->cards->data [$omise_customer->cards->total - 1]->id;
						} else {
							$description = "WooCommerce customer " . $user->id;
							$customer_data = array (
									"description" => $description,
									"card" => $token 
							);
							
							$omise_customer = Omise::create_customer ( $this->private_key, $customer_data );
							
							if($omise_customer->object=="error"){
								throw new Exception($omise_customer->message);
							}
							
							$omise_customer_id = $omise_customer->id;
							if($this->sandbox){
								update_user_meta ( $user->ID, 'test_omise_customer_id', $omise_customer_id );
							}else{
								update_user_meta ( $user->ID, 'live_omise_customer_id', $omise_customer_id );
							}
							
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
						"return_uri" => add_query_arg( 'order_id', $order_id, site_url()."?wc-api=wc_gateway_omise" )
					);
					
					if (! empty ( $card_id ) && ! empty ( $omise_customer_id )) {
						// create charge with a specific card of customer
						$data["customer"] =  $omise_customer_id;
						$data["card"] = $card_id;
					} else if (! empty ( $token )) {
						$data["card"] = $token;
					} else {
						throw new Exception ( "Please select a card or enter new payment information." );
					}

					$result = Omise::create_charge($this->private_key, $data);
					if ($result->object === "error") {
						throw new Exception ($result->message);
					} if ($result->failure_code) {
						throw new Exception ($result->failure_message);
					} else {
						$post_id = wp_insert_post(array(
							'post_title' 	=> 'Omise Charge Id '.$result->id,
							'post_type'		=> 'omise_charge_items',
							'post_status'	=> 'publish'
						));

						add_post_meta($post_id, '_omise_charge_id', $result->id);
						add_post_meta($post_id, '_wc_order_id', $order_id);
						add_post_meta($post_id, '_wc_confirmed_url', $this->get_return_url($order));
					}
					
					$success = false;
					if ($this->omise_3ds) {
						return array (
							'result' => 'success',
							'redirect' => $result->authorize_uri
						);
					} else {
						$success = $this->is_charge_success($result);
					}

					if ($success) {
						$order->payment_complete ();
						$order->add_order_note ( 'Payment with Omise successful' );
						// Remove cart
						WC()->cart->empty_cart();
						return array (
								'result' => 'success',
								'redirect' => $this->get_return_url ( $order ) 
						);
					} else {
						throw new Exception($this->get_charge_error_message($result));
					}
				}
				catch( Exception $e ){
					$error_message = $e->getMessage();
					wc_add_notice( __('Payment error:', 'woothemes') . $error_message , 'error' );
					$order->add_order_note ( 'Payment with Omise error : '. $error_message );
					return array(
							'result'   => 'fail',
							'redirect' => ''
					);
				}
			}
			
			private function is_charge_success($result){
				return isset ( $result->id ) && isset( $result->object ) && $result->object == 'charge' && $result->captured == true;
			}
			
			private function get_charge_error_message($result){
				$message = "";
				
				if(isset($result->message) && !empty($result->message)){
					$message .= $result->message." ";
				}
				
				if(isset($result->failure_code) && !empty($result->failure_code)){
					$message .= "[".$result-> failure_code."] ";
				}
				
				if(isset($result->failure_message) && !empty($result->failure_message)){
					$message .= $result-> failure_message;
				}
				
				return trim($message);
			}

			/**
			 * Get icons of all supported card types
			 * @see WC_Payment_Gateway::get_icon()
			 */
			public function get_icon() {
				$icon = '<img src="' . WC_HTTPS::force_https_url ( WC ()->plugin_url () . '/assets/images/icons/credit-cards/visa.png' ) . '" alt="Visa" />';
				$icon .= '<img src="' . WC_HTTPS::force_https_url ( WC ()->plugin_url () . '/assets/images/icons/credit-cards/mastercard.png' ) . '" alt="Mastercard" />';
				
				return apply_filters ( 'woocommerce_gateway_icon', $icon, $this->id );
			}

			/**
			 * Register all javascripts
			 */
			public function omise_scripts() {
				if (! is_checkout () || ! $this->is_available ()) {
					return;
				}
				
				wp_enqueue_style('omise-css', plugins_url ( '/assets/css/omise-css.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION);
				wp_enqueue_script ( 'omise-js', 'https://cdn.omise.co/omise.js', array (
						'jquery' 
				), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
				wp_enqueue_script ( 'omise-util', plugins_url ( '/assets/javascripts/omise-util.js', __FILE__ ), array (
				'omise-js'), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
				wp_enqueue_script ( 'omise-payment-form-handler', plugins_url ( '/assets/javascripts/omise-payment-form-handler.js', __FILE__ ), array (
						'omise-js', 'omise-util'), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
				wp_localize_script ( 'omise-payment-form-handler', 'omise_params', array (
						'key' => $this->public_key,
						'vault_url' => OMISE_VAULT_HOST
				) );
			}

			public function omise_3ds_handler()
			{
				if (!$_GET['order_id'])
					die("order_id");

				$order_id 	= $_GET['order_id'];

				$posts = get_posts(array(
					'post_type'		=> 'omise_charge_items',
					'meta_query' 	=> array(array(
						'key' 		=> '_wc_order_id',
						'value' 	=> $order_id,
						'compare'	=> 'LIKE'
					))
				));

				if (empty($posts))
					die("charge was not found");

				$order = wc_get_order($order_id);
				if (!$order)
					die("order was not found");

				$confirmed_url 	= get_post_custom_values('_wc_confirmed_url', $posts[0]->ID);
				$confirmed_url 	= $confirmed_url[0];

				$charge_id 		= get_post_custom_values('_omise_charge_id', $posts[0]->ID);
				$charge_id 		= $charge_id[0];

				$result = Omise::get_charges($this->private_key, $charge_id);

				if ($this->is_charge_success($result)) {
					$order->payment_complete();
					$order->add_order_note('Payment with Omise successful');

					// Remove cart
					WC()->cart->empty_cart();

					header("Location: ".$confirmed_url);
				}

				die();
			}
		}
	}

	function add_omise_gateway($methods) {
		$methods [] = 'WC_Gateway_Omise';
		return $methods;
	}
	
	add_filter ( 'woocommerce_payment_gateways', 'add_omise_gateway' );
}

function register_omise_wc_gateway_post_type() {
	register_post_type('omise_charge_items', array(
		'label' => 	'Omise Charge Items',
		'labels' =>	array(
			'name' => 'Omise Charge Items',
			'singular_name' => 'Omise Charge Item'
		),
		'public'	=> true,
		'supports'	=> array('title','custom-fields')
	));
}
?>
