<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Admin' ) ) {
	class Omise_Admin {

		private static $instance;

		/**
		 * @var string
		 */
		private $private_key;

		/**
		 * @var string
		 */
		private $payment_action;

		/**
		 * @var string ('yes' or 'no')
		 */
		private $support_3dsecure;

		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register Omise to WordPress, WooCommerce
		 * @return void
		 */
		public function register_admin_page_and_actions() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			add_action( 'admin_post_omise_create_transfer', array( $this, 'create_transfer' ) );
			add_action( 'admin_post_nopriv_omise_create_transfer', array( $this, 'no_op' ) );
			add_action( 'admin_menu', array( $this, 'add_dashboard_omise_menu' ) );
		}

		/**
		 * Add Omise menu to sidebar admin menu
		 * @return void
		 */
		public function add_dashboard_omise_menu() {
			add_menu_page( 'Omise', 'Omise', 'manage_options', 'omise-plugin-admin-page', array( $this, 'render_dashboard_page' ) );
			add_submenu_page( 'omise-plugin-admin-page', 'Omise Dashboard', _x( 'Dashboard', 'Menu', 'omise' ), 'manage_options', 'omise-plugin-admin-page' );
			add_submenu_page( 'omise-plugin-admin-page', 'Omise Transfers', _x( 'Transfers', 'Menu', 'omise' ), 'manage_options', 'omise-plugin-admin-transfer-page', array( $this, 'render_transfers_page' ) );
			add_submenu_page( 'omise-plugin-admin-page', 'Omise Setting', _x( 'Setting', 'Menu', 'omise' ), 'manage_options', 'wc-settings&tab=checkout&section=omise' , function(){} );
		}

		private function __construct() {
			$settings = get_option( "woocommerce_omise_settings", null );

			if ( is_null( $settings ) || ! is_array( $settings ) ) {
				return;
			}

			$this->test_mode = isset( $settings["sandbox"] ) && $settings["sandbox"] == 'yes';

			if ( empty( $settings["test_private_key"] ) && $this->test_mode ) {
				return;
			}

			if ( empty( $settings["live_private_key"] ) && ! $this->test_mode ) {
				return;
			}

			$this->private_key      = $this->test_mode ? $settings ["test_private_key"] : $settings ["live_private_key"];
			$this->payment_action   = $settings['payment_action'];
			$this->support_3dsecure = $settings['omise_3ds'];

			if ( empty( $this->private_key ) ) {
				return;
			}
		}

		public function no_op() {
			exit( "Not permitted" );
		}

		public function create_transfer() {
			if ( ! wp_verify_nonce( $_POST['omise_create_transfer_nonce'], 'omise_create_transfer' ) ) {
				die( 'Nonce verification failure' );
			}

			if ( ! isset( $_POST['_wp_http_referer'] ) ) {
				die( 'Missing target' );
			}

			$transfer_amount = isset( $_POST['omise_transfer_amount'] ) ? $_POST['omise_transfer_amount'] : '';
			$result_message  = '';

			try {
				if ( ! empty( $transfer_amount ) && ! is_numeric( $transfer_amount ) ) {
					throw new Exception ( __( 'Transfer amount must be a numeric', 'omise' ) );
				}

				$balance = OmiseBalance::retrieve( '', $this->private_key );
				if ( strtoupper( $balance['currency'] ) === "THB" ) {
					$transfer_amount = $transfer_amount * 100;
				}

				$data = array(
					'amount' => empty( $transfer_amount ) ? null : $transfer_amount
				);
				$transfer = OmiseTransfer::create( $data, '', $this->private_key );

				if ( $this->is_transfer_success( $transfer ) ) {
					$result_message_type = 'updated';
					$result_message      = __( 'A fund transfer request has been sent.', 'omise' );
				} else {
					$result_message_type = 'error';
					$result_message      = $this->get_transfer_error_message($transfer);
				}
			} catch ( Exception $e ) {
				$result_message_type = 'error';
				$result_message      = $e->getMessage();
			}

			$url = add_query_arg(
				array(
					'omise_result_msg_type' => $result_message_type,
					'omise_result_msg'      => urlencode( $result_message )
				),
				esc_url( add_query_arg( array( 'page' => 'omise-plugin-admin-transfer-page' ), admin_url( 'admin.php' ) ) )
			);

			wp_safe_redirect( $url );
			exit();
		}

		private function is_transfer_success( $transfer ) {
			return isset( $transfer['id'] ) && isset( $transfer['object'] ) && $transfer['object'] == 'transfer' && $transfer['failure_code'] == null && $transfer['failure_message'] == null;
		}

		private function get_transfer_error_message( $transfer ) {
			$message = "";

			if( isset( $transfer['message'] ) && ! empty( $transfer['message'] ) ) {
				$message .= $transfer['message'] . " ";
			}

			if ( isset( $transfer['failure_code'] ) && ! empty( $transfer['failure_code'] ) ) {
				$message .= "[" . $transfer['failure_code'] . "] ";
			}

			if ( isset( $transfer['failure_message'] ) && ! empty( $transfer['failure_message'] ) ) {
				$message .= $transfer['failure_message'];
			}

			return trim($message);
		}

		/**
		 * Retrieve and prepare balance and account information
		 * @return mixed
		 */
		function init() {
			try {
				$balance = OmiseBalance::retrieve( '', $this->private_key );

				if ( $balance['object'] == 'balance' ) {
					$omise_account = OmiseAccount::retrieve( '', $this->private_key );

					$viewData['auto_capture']     = $this->payment_action === 'auto_capture' ? 'YES' : 'NO';
					$viewData['support_3dsecure'] = $this->support_3dsecure === 'yes' ? 'ENABLED' : 'DISABLED';
					$viewData['balance']          = $balance;
					$viewData['email']            = $omise_account['email'];

					return $viewData;
				} else {
					$message = sprintf( __( 'Unable to get the balance information. Please verify that your secret key is valid. [%s]', 'omise' ), esc_html( $balance['message'] ) );
					echo "<div class='wrap'><div class='error'>$message</div></div>";
				}
			} catch ( Exception $e ) {
				echo "<div class='wrap'><div class='error'>" . esc_html( $e->getMessage() ) . "</div></div>";
			}
		}

		/**
		 * Retrieve charges and render page
		 * @return void
		 */
		public function render_dashboard_page() {
			$viewData = $this->init();

			try {
				$viewData['charges'] = Omise_Charge::list_charges( $this->private_key );
				$this->render_view( 'includes/admin/views/page-dashboard.php', $viewData );
			} catch( Exception $e ) {
				echo "<div class='wrap'><div class='error'>" . esc_html( $e->getMessage () ) . "</div></div>";
			}
		}

		/**
		 * Retrieve transfers and render page
		 * @return void
		 */
		public function render_transfers_page() {
			$viewData = $this->init();

			try {
				$viewData['transfers'] = Omise_Transfer::list_transfers( $this->private_key );
				$this->render_view( 'includes/admin/views/page-transfer.php', $viewData );
			} catch ( Exception $e ) {
				echo "<div class='wrap'><div class='error'>" . esc_html( $e->getMessage() ) . "</div></div>";
			}
		}

		function render_view( $view, $viewData ) {
			$this->extract_result_message( $viewData );

			Omise_Util::render_view( $view, $viewData );

			$this->register_dashboard_script();
		}

		function extract_result_message( &$viewData ) {
			if ( isset( $_GET['omise_result_msg'] ) ) {
				$viewData["message"]      = $_GET['omise_result_msg'];
				$viewData["message_type"] = isset( $_GET['omise_result_msg_type'] ) ? $_GET['omise_result_msg_type'] : 'updated';
			} else {
				$viewData["message"]      = '';
				$viewData["message_type"] = '';
			}
		}

		function register_dashboard_script() {
			wp_enqueue_script( 'omise-dashboard-js', plugins_url( '/assets/javascripts/omise-dashboard-handler.js', __FILE__ ), array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
			wp_enqueue_style( 'omise-css', plugins_url( '/assets/css/omise-css.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );
		}
	}
}
?>
