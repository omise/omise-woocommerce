<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( ! class_exists( 'Omise_MyAccount' ) ) {
	#[AllowDynamicProperties]
	class Omise_MyAccount
	{
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

			$this->customerCard = new OmiseCustomerCard;
			$this->omiseCardGateway = new Omise_Payment_Creditcard();

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
					$viewData['existingCards'] = $this->customerCard->get($this->omise_customer_id);
					$viewData['cardFormTheme'] = $this->omiseCardGateway->get_option('card_form_theme');
					$viewData['secure_form_enabled'] = (boolean)$this->omiseCardGateway->get_option('secure_form_enabled');
					$viewData['formDesign'] = Omise_Page_Card_From_Customization::get_instance()->get_design_setting();
					$viewData['cardIcons'] = $this->omiseCardGateway->get_card_icons();
					$this->register_omise_my_account_scripts();

					Omise_Util::render_view( 'templates/myaccount/my-card.php', $viewData );
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
				Omise::OMISE_JS_LINK,
				array( 'jquery' ),
				WC_VERSION,
				true
			);

			wp_enqueue_script(
				'embedded-js',
				plugins_url( '/assets/javascripts/omise-embedded-card.js', dirname( __FILE__ ) ),
				[],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);

			wp_enqueue_script(
				'omise-myaccount-card-handler',
				plugins_url( '/assets/javascripts/omise-myaccount-card-handler.js', dirname( __FILE__ ) ),
				array( 'omise-js' ),
				WC_VERSION,
				true
			);

			wp_localize_script(
				'omise-myaccount-card-handler',
				'omise_params',
				$this->getParamsForJS()
			);
		}

		/**
		 * Parameters to be passed directly to the JavaScript file.
		 */
		public function	getParamsForJS()
		{
			return [
				'key'                            => Omise()->settings()->public_key(),
				'ajax_url'                       => admin_url( 'admin-ajax.php' ),
				'ajax_loader_url'                => plugins_url( '/assets/images/ajax-loader@2x.gif', dirname( __FILE__ ) ),
				'required_card_name'             => __( "Cardholder's name is a required field", 'omise' ),
				'required_card_number'           => __( 'Card number is a required field', 'omise' ),
				'required_card_expiration_month' => __( 'Card expiry month is a required field', 'omise' ),
				'required_card_expiration_year'  => __( 'Card expiry year is a required field', 'omise' ),
				'required_card_security_code'    => __( 'Card security code is a required field', 'omise' ),
				'cannot_create_card'             => __( 'Unable to add a new card.', 'omise' ),
				'cannot_connect_api'             => __( 'Currently, the payment provider server is undergoing maintenance.', 'omise' ),
				'cannot_load_omisejs'            => __( 'Cannot connect to the payment provider.', 'omise' ),
				'check_internet_connection'      => __( 'Please make sure that your internet connection is stable.', 'omise' ),
				'retry_or_contact_support'       => wp_kses(
					__( 'This incident could occur either from the use of an invalid card, or the payment provider server is undergoing maintenance.<br/>You may retry again in a couple of seconds, or contact our support team if you have any questions.', 'omise' ),
					[ 'br' => [] ]
				),
				'expiration date cannot be in the past' => __( 'expiration date cannot be in the past', 'omise' ),
				'expiration date cannot be in the past and number is invalid' => __( 'expiration date cannot be in the past and number is invalid', 'omise' ),
				'expiration date cannot be in the past, number is invalid, and brand not supported (unknown)' => __( 'expiration date cannot be in the past, number is invalid, and brand not supported (unknown)', 'omise' ),
				'number is invalid and brand not supported (unknown)' => __( 'number is invalid and brand not supported (unknown)', 'omise' ),
				'expiration year is invalid, expiration date cannot be in the past, number is invalid, and brand not supported (unknown)' => __( 'expiration year is invalid, expiration date cannot be in the past, number is invalid, and brand not supported (unknown)', 'omise' ),
				'expiration month is not between 1 and 12, expiration date is invalid, number is invalid, and brand not supported (unknown)' => __('expiration month is not between 1 and 12, expiration date is invalid, number is invalid, and brand not supported (unknown)', 'omise'),
				'secure_form_enabled'	=> (boolean)$this->omiseCardGateway->get_option('secure_form_enabled')
			];
		}

		/**
		 * Public omise_delete_card ajax hook
		 */
		public function omise_delete_card()
		{
			$cardId = isset( $_POST['card_id'] ) ? wc_clean( $_POST['card_id'] ) : '';

			if ( empty( $cardId ) ) {
				Omise_Util::render_json_error( 'card_id is required' );
				die();
			}

			$nonce = 'omise_delete_card_' . $_POST['card_id'];

			if ( ! wp_verify_nonce( $_POST['omise_nonce'], $nonce ) ) {
				Omise_Util::render_json_error( 'Nonce verification failure' );
				die();
			}

			$cardDeleted = $this->customerCard->delete($cardId, $this->omise_customer_id);

			echo json_encode([ 'deleted' => $cardDeleted ]);
			die();
		}

		/**
		 * Public omise_create_card ajax hook
		 */
		public function omise_create_card()
		{
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
				$card = $this->customerCard->create($this->omise_customer_id, $token);
				echo json_encode( $card );
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
