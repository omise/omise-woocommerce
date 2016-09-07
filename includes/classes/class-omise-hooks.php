<?php
defined( 'ABSPATH' ) or die ( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Hooks' ) ) {
	class Omise_Hooks {

		private static $instance;

		/**
		 * @var boolean
		 */
		private $test_mode;

		/**
		 * @var string
		 */
		private $payment_action;

		/**
		 * @var boolean
		 */
		private $support_3dsecure;

		/**
		 * @var string
		 */
		private $secret_key;

		/**
		 * @return void
		 */
		private function __construct() {
			$settings = get_option( 'woocommerce_omise_settings', null );

			if ( is_null( $settings ) || ! is_array( $settings ) )
				return;

			$this->test_mode        = isset( $settings['sandbox'] ) && $settings['sandbox'] === 'yes';
			$this->payment_action   = $settings['payment_action'];
			$this->support_3dsecure = isset( $settings['omise_3ds'] ) && $settings['omise_3ds'] === 'yes';
			$this->secret_key       = $this->test_mode ? $settings['test_private_key'] : $settings['live_private_key'];
		}

		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Capture an authorized charge.
		 * @param WC_Order $order WooCommerce's order object
		 * @return void
		 */
		public function charge_capture( $order ) {
			Omise_Hooks::set_omise_user_agent();

			try {
				$post = Omise_Charge::get_post_charge( $order->id );
				if ( ! $post )
					throw new Exception( Omise_Util::translate( 'Order id was not found' ) );

				$id = Omise_Charge::get_charge_id_from_post( $post );
				if ( is_null( $id ) || $id === '' )
					throw new Exception( Omise_Util::translate( 'Charge id was not found' ) );

				$charge = OmiseCharge::retrieve( $id, '', $this->secret_key );
				$charge->capture();

				if ( ! OmisePluginHelperCharge::isPaid( $charge ) )
					throw new Exception( $charge['failure_message'] );

				$order->payment_complete();
				$order->add_order_note( Omise_Util::translate( 'Payment with Omise successful (manual captured)' ) );
			} catch ( Exception $e ) {
				$order->add_order_note( $e->getMessage() );
			}
		}

		/**
		 * Callback method after redirect back from the 3D-Secure page
		 * @return void
		 */
		public function charge_3ds_callback() {
			Omise_Hooks::set_omise_user_agent();

			try {
				if ( ! isset( $_GET['order_id'] ) )
					throw new Exception( Omise_Util::translate( 'Order was not found. Please check carefully, your card might be charged already, contact our support if possible.' ) );

				$order_id = $_GET['order_id'];

				// Looking for WC_Order object
				$order = wc_get_order( $order_id );
				if ( ! $order )
					throw new Exception( Omise_Util::translate( 'Order was not found. Please check carefully, your card might be charged already, contact our support if possible.' ) );

				// Looking for WP_Post object
				$post = Omise_Charge::get_post_charge( $order_id );
				if ( ! $post )
					throw new Exception( Omise_Util::translate( 'Order id was not found' ) );

				// Looking for Omise's charge id
				$charge_id = Omise_Charge::get_charge_id_from_post( $post );
				if ( $charge_id === '' )
					throw new Exception( Omise_Util::translate( 'Charge id was not found' ) );

				// Looking for WC's confirm url
				$confirmed_url = Omise_Charge::get_confirmed_url_from_post( $post );
				if ( $confirmed_url === '' )
					throw new Exception( Omise_Util::translate( 'Confirm url was not found' ) );

				$charge = OmiseCharge::retrieve( $charge_id, '', $this->secret_key );
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
					throw new Exception( Omise_Charge::get_error_message( $charge ) );

				// Remove cart
				WC()->cart->empty_cart();
				header( "Location: " . $confirmed_url );
				die();
			} catch ( Exception $e ) {
				if ( isset( $order ) )
					$order->add_order_note( Omise_Util::translate( 'Charge was not completed' ) . ', ' . $e->getMessage() );

				wc_add_notice( Omise_Util::translate( 'Payment error' ) . ': ' . $e->getMessage() , 'error' );
				header( "Location: " . WC()->cart->get_checkout_url() );
				die();
			}

			wp_die( "Access denied", "Access Denied", array( 'response' => 401 ) );
			die();
		}

		public static function set_omise_user_agent() {
			// Set OMISE_USER_AGENT_SUFFIX
			global $wp_version;

			$user_agent  = "OmiseWooCommerce/" . OMISE_WOOCOMMERCE_PLUGIN_VERSION;
			$user_agent .= " WordPress/" . $wp_version;
			$user_agent .= " WooCommerce/" . WC_VERSION;
			defined( 'OMISE_USER_AGENT_SUFFIX' ) || define( 'OMISE_USER_AGENT_SUFFIX', $user_agent );
		}
	}
}