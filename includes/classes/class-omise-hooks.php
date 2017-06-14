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
					throw new Exception( __( 'Order id was not found', 'omise' ) );

				$id = Omise_Charge::get_charge_id_from_post( $post );
				if ( is_null( $id ) || $id === '' )
					throw new Exception( __( 'Charge id was not found', 'omise' ) );

				$charge = OmiseCharge::retrieve( $id, '', $this->secret_key );
				$charge->capture();

				if ( ! OmisePluginHelperCharge::isPaid( $charge ) )
					throw new Exception( $charge['failure_message'] );

				$order->payment_complete();
				$order->add_order_note( __( 'Payment with Omise successful (manual captured)', 'omise' ) );
			} catch ( Exception $e ) {
				$order->add_order_note( $e->getMessage() );
			}
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