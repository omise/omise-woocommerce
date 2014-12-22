<?php
defined ( 'ABSPATH' ) or die ( "No direct script access allowed." );

if (! class_exists ( 'Omise_MyAccount' )) {
	class Omise_MyAccount {
		private static $instance;
		private $private_key, $public_key, $omise_customer_id;
		public static function get_instance() {
			if (! self::$instance) {
				self::$instance = new self ();
			}
			return self::$instance;
		}

		private function __construct() {
			
			// prevent running directly without wooCommerce
			if (! class_exists ( 'WC_Payment_Gateway' ))
				return;
			
			$settings = get_option ( "woocommerce_omise_settings", null );
			
			if ( is_null($settings) || ! is_array ( $settings )) {
				throw new Exception ( "Cannot load omise settings" );
			}
			
			if (empty ( $settings ["sandbox"] ) || empty ( $settings ["test_private_key"] ) || empty ( $settings ["live_private_key"] )) {
				throw new Exception ( "Incomplete gateway settings" );
			}
			
			$this->private_key = $settings ["sandbox"] ? $settings ["test_private_key"] : $settings ["live_private_key"];
			$this->public_key = $settings ["sandbox"] ? $settings ["test_public_key"] : $settings ["live_public_key"];
			
			if (empty ( $this->private_key ) || empty ( $this->public_key )) {
				throw new Exception ( "Incomplete gateway settings" );
			}
			
			if (is_user_logged_in ()) {
				$current_user = wp_get_current_user ();
				$this->omise_customer_id = $current_user->omise_customer_id;
			}
			
			add_action ('woocommerce_after_my_account',array($this, 'init_panel'));
			add_action ( 'wp_ajax_omise_delete_card', array($this, 'omise_delete_card'));
			add_action ( 'wp_ajax_omise_create_card', array($this, 'omise_create_card'));
			add_action ( 'wp_ajax_nopriv_omise_delete_card', array($this, 'no_op'));
			add_action ( 'wp_ajax_nopriv_omise_create_card', array($this, 'no_op'));
			
		}
		
		public function init_panel(){
			
				if (! empty ( $this->omise_customer_id )) {
					$cards = Omise::get_customer_cards ( $this->private_key, $this->omise_customer_id );
					$viewData ["existingCards"] = $cards;
						
					Omise_Util::render_view ( 'includes/templates/omise-myaccount-panel.php', $viewData );
					$this->register_omise_my_account_scripts ();
				}
		}

		public function register_omise_my_account_scripts() {
			wp_enqueue_script ( 'omise-js', 'https://cdn.omise.co/omise.js', array (
					'jquery' 
			), WC_VERSION, true );
			wp_enqueue_script ( 'omise-util', plugins_url ( '/assets/javascripts/omise-util.js', __FILE__ ), array (
			'omise-js'), WC_VERSION, true );
			wp_enqueue_script ( 'omise-myaccount-card-handler', plugins_url ( '/assets/javascripts/omise-myaccount-card-handler.js', __FILE__ ), array (
					'omise-js' , 'omise-util'
			), WC_VERSION, true );
			wp_localize_script ( 'omise-myaccount-card-handler', 'omise_params', array (
					'key' => $this->public_key,
					'ajax_url' => admin_url ( 'admin-ajax.php' ) ,
					'ajax_loader_url' => plugins_url ( '/assets/images/ajax-loader@2x.gif', __FILE__ )
			) );
		}
		/**
		 * Public omise_delete_card ajax hook
		 */
		public function omise_delete_card() {
			$card_id = isset ( $_POST ['card_id'] ) ? wc_clean ( $_POST ['card_id'] ) : '';
			if (empty ( $card_id )) {
				Omise_Util::render_json_error("card_id is required");
				//echo json_encode('{ "object": "error", "message": "card_id is required" }');
				die();
			}
			
			$nonce = 'omise_delete_card_' . $_POST['card_id'];
			if (! wp_verify_nonce($_POST['omise_nonce'], $nonce)) {
				Omise_Util::render_json_error("Nonce verified failure");
				//echo json_encode('{ "object": "error", "message": "Nonce verified failure" }');
				die();
			}
			
			$result = Omise::delete_card ( $this->private_key, $this->omise_customer_id, $card_id );
			echo $result;
			die();
		}

		public function omise_create_card() {
			$token = isset ( $_POST ['omise_token'] ) ? wc_clean ( $_POST ['omise_token'] ) : '';
			if (empty ( $token )) {
				Omise_Util::render_json_error("omise_token is required");
				//echo json_encode('{ "object": "error", "message": "omise_token is required" }');
				die();
			}
			
			if (! wp_verify_nonce($_POST['omise_nonce'], 'omise_add_card')) {
				Omise_Util::render_json_error("Nonce verified failure");
				//echo json_encode('{ "object": "error", "message": "Nonce verified failure" }');
				die();
			}
			
			$card = Omise::create_card (  $this->private_key, $this->omise_customer_id, $token );
			echo $card;
			die();
		}

		public function no_op() {
			exit ( 'Not permitted' );
		}
	}
}

function prepare_omise_myaccount_panel() {
	$omise_myaccount = Omise_MyAccount::get_instance();
}
?>