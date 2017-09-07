<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'Omise_Admin' ) ) {
	class Omise_Admin {
		/**
		 * The Omise Instance.
		 *
		 * @var   \Omise_Admin
		 */
		protected static $the_instance;

		public static function get_instance() {
			if ( ! self::$the_instance ) {
				self::$the_instance = new self();
			}

			return self::$the_instance;
		}

		/**
		 * Register Omise to WordPress, WooCommerce
		 * @return void
		 */
		public function register_admin_menu() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		}

		/**
		 * Add Omise menu to sidebar admin menu
		 * @return void
		 */
		public function add_admin_menu() {
			add_menu_page( 'Omise', 'Omise', 'manage_options', 'omise', array( $this, 'page_settings') );

			add_submenu_page( 'omise', __( 'Omise Settings', 'omise' ), __( 'Settings', 'omise' ), 'manage_options', 'omise-settings', array( $this, 'page_settings') );
		}

		public function page_settings() {
			Omise_Page_Settings::render();
		}
	}
}
?>
