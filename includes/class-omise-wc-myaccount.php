<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( ! class_exists( 'Omise_MyAccount' ) ) {
	class Omise_MyAccount {
		private static $instance;
		private $omise_customer_id;

		public static function get_instance() {
			if ( ! self::$instance) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			// prevent running directly without wooCommerce
			if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
				return;
			}

			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				$this->omise_customer_id = Omise()->settings()->is_test() ? $current_user->test_omise_customer_id : $current_user->live_omise_customer_id;
			}

			add_action( 'woocommerce_after_my_account', array( $this, 'init_panel' ) );
			add_action( 'wp_ajax_omise_delete_card', array( $this, 'omise_delete_card' ) );
			add_action( 'wp_ajax_omise_create_card', array( $this, 'omise_create_card' ) );
			add_action( 'wp_ajax_nopriv_omise_delete_card', array( $this, 'no_op' ) );
			add_action( 'wp_ajax_nopriv_omise_create_card', array( $this, 'no_op' ) );
		}

		/**
		 * Append Omise Settings panel to My Account page
		 */
		public function init_panel() {
			if ( ! empty( $this->omise_customer_id ) ) {
				try {
					$customer                  = OmiseCustomer::retrieve( $this->omise_customer_id );
					$viewData['existingCards'] = $customer->cards();

					Omise_Util::render_view( 'templates/myaccount/my-card.php', $viewData );
					$this->register_omise_my_account_scripts();
				} catch (Exception $e) {
					// nothing.
				}
			}
		}

		/**
		 * Register all javascripts
		 */
		public function register_omise_my_account_scripts() {
			wp_enqueue_script(
				'omise-js',
				'https://cdn.omise.co/omise.js',
				array( 'jquery' ),
				WC_VERSION,
				true
			);

			wp_enqueue_script(
				'omise-myaccount-card-handler',
				plugins_url( '/assets/javascripts/omise-myaccount-card-handler.js', dirname( __FILE__ ) ),
				array( 'omise-js' ),
				WC_VERSION,
				true
			);

			$omise_params = array(
				'key'                            => Omise()->settings()->public_key(),
				'ajax_url'                       => admin_url( 'admin-ajax.php' ),
				'ajax_loader_url'                => plugins_url( '/assets/images/ajax-loader@2x.gif', dirname( __FILE__ ) ),
				'required_card_name'             => __( 'Cardholder\'s name is a required field', 'omise' ),
				'required_card_number'           => __( 'Card number is a required field', 'omise' ),
				'required_card_expiration_month' => __( 'Card expiry month is a required field', 'omise' ),
				'required_card_expiration_year'  => __( 'Card expiry year is a required field', 'omise' ),
				'required_card_security_code'    => __( 'Card security code is a required field', 'omise' ),
				'cannot_create_card'             => __( 'Unable to add a new card.', 'omise' ),
				'cannot_connect_api'             => __( 'Currently, the payment provider server is undergoing maintenance.', 'omise' ),
				'cannot_load_omisejs'            => __( 'Cannot connect to the payment provider.', 'omise' ),
				'check_internet_connection'      => __( 'Please make sure that your internet connection is stable.', 'omise' ),
				'retry_or_contact_support'       => wp_kses(
					__( 'This incident could occur either from the use of an invalid card, or the payment provider server is undergoing maintenance.<br/>
					    You may retry again in a couple of seconds, or contact our support team if you have any questions.', 'omise' ),
					array( 'br' => array() )
				)
			);

			wp_localize_script( 'omise-myaccount-card-handler', 'omise_params', $omise_params );
		}

		/**
		 * Public omise_delete_card ajax hook
		 */
		public function omise_delete_card() {
			$card_id = isset( $_POST['card_id'] ) ? wc_clean( $_POST['card_id'] ) : '';
			if ( empty( $card_id ) ) {
				Omise_Util::render_json_error( 'card_id is required' );
				die();
			}

			$nonce = 'omise_delete_card_' . $_POST['card_id'];
			if ( ! wp_verify_nonce( $_POST['omise_nonce'], $nonce ) ) {
				Omise_Util::render_json_error( 'Nonce verification failure' );
				die();
			}

			$customer = OmiseCustomer::retrieve( $this->omise_customer_id );
			$card     = $customer->cards()->retrieve( $card_id );
			$card->destroy();

			echo json_encode( array(
				'deleted' => $card->isDestroyed()
			) );
			die();
		}

		/**
		 * Public omise_create_card ajax hook
		 */
		public function omise_create_card() {
			$token = isset ( $_POST['omise_token'] ) ? wc_clean ( $_POST['omise_token'] ) : '';
			if ( empty( $token ) ) {
				Omise_Util::render_json_error( 'omise_token is required' );
				die();
			}

			if ( ! wp_verify_nonce($_POST['omise_nonce'], 'omise_add_card' ) ) {
				Omise_Util::render_json_error( 'Nonce verification failure' );
				die();
			}

			try {
				$customer = OmiseCustomer::retrieve( $this->omise_customer_id );
				$customer->update( array(
					'card' => $token
				) );

				$cards = $customer->cards( array(
					'limit' => 1,
					'order' => 'reverse_chronological'
				) );

				echo json_encode( $cards['data'][0] );
			} catch( Exception $e ) {
				echo json_encode( array(
					'object'  => 'error',
					'message' => $e->getMessage()
				) );
			}

			die();
		}

		/**
		 * No operation on no-priv ajax requests
		 */
		public function no_op() {
			exit( 'Not permitted' );
		}
	}
}

function prepare_omise_myaccount_panel() {
	$omise_myaccount = Omise_MyAccount::get_instance();
}
?>
