<?php

defined( "ABSPATH" ) or die( "No direct script access allowed." );

if ( ! class_exists( "Omise_Admin" ) ) {
    class Omise_Admin {
        private static $instance;
        private $private_key;

        private function __construct() {
            $settings = get_option( "woocommerce_omise_settings", null );

            if ( is_null( $settings ) || ! is_array( $settings ) ) {
                return;
            }

            $this->test_mode = isset ( $settings["sandbox"] ) && "yes" == $settings["sandbox"];

            if ( empty ( $settings["test_private_key"] ) && $this->test_mode ) {
                return;
            }

            if ( empty ( $settings["live_private_key"] ) && ! $this->test_mode ) {
                return;
            }

            $this->private_key = $this->test_mode ? $settings["test_private_key"] : $settings["live_private_key"];

            if ( empty( $this->private_key ) ) {
                return;
            }
        }

        public static function get_instance() {
            if ( ! self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function register_admin_page_and_actions() {
            if ( ! current_user_can( "manage_options" ) ) {
                return;
            }

            add_action( "admin_post_omise_create_transfer", array (
                $this,
                "create_transfer"
            ) );

            add_action( "admin_post_nopriv_omise_create_transfer", array (
                $this,
                "no_op"
            ) );

            add_action ( "admin_menu", array (
                $this,
                "add_dashboard_omise_menu"
            ) );
        }

        public function add_dashboard_omise_menu() {
            add_menu_page ( "Omise", "Omise", "manage_options", "omise-plugin-admin-page", array (
                $this,
                "init_dashboard"
            ) );
        }

        public function no_op(){
            exit( "Not permitted" );
        }

        public function create_transfer() {
            if ( ! wp_verify_nonce( $_POST["omise_create_transfer_nonce"], "omise_create_transfer" ) )
                die ( "Nonce verification failure" );

            if ( ! isset( $_POST["_wp_http_referer"] ) )
                die( "Missing target" );

            $transfer_amount = isset( $_POST["omise_transfer_amount"] ) ? $_POST["omise_transfer_amount"] : "";
            $result_message = "";
            try {
                if ( ! empty( $transfer_amount ) && ! is_numeric( $transfer_amount ) ) {
                    throw new Exception( "Transfer amount must be a numeric" );
                }

                $transfer = Omise::create_transfer( $this->private_key, empty ( $transfer_amount ) ? null : $transfer_amount * 100 ); // transfer in satangs

                if ( $this->is_transfer_success( $transfer ) ) {
                    $result_message = "A fund transfer request has been sent.";
                } else {
                    $result_message = $this->get_transfer_error_message( $transfer );
                }

            } catch ( Exception $e ) {
                $result_message = $e->getMessage();
            }

            $url = add_query_arg ( "omise_result_msg", urlencode ( $result_message ), urldecode ( $_POST["_wp_http_referer"] ) );

            wp_safe_redirect( $url );
            exit ();
        }

        private function is_transfer_success( $transfer ) {
            return isset( $transfer->id ) && isset( $transfer->object ) && "transfer" == $transfer->object && $transfer->failure_code == null && $transfer->failure_message == null;
        }

        private function get_transfer_error_message( $transfer ) {
            $message = "";

            if ( isset( $transfer->message ) && ! empty( $transfer->message ) ) {
                $message .= $transfer->message . " ";
            }

            if ( isset( $transfer->failure_code ) && ! empty( $transfer->failure_code ) ) {
                $message .= "[" . $transfer->failure_code . "] ";
            }

            if ( isset( $transfer->failure_message ) && ! empty( $transfer->failure_message ) ) {
                $message .= $transfer->failure_message;
            }

            return trim( $message );
        }

        public function init_dashboard() {
            try {
                $balance = Omise::get_balance( $this->private_key );
                if ( "balance" == $balance->object ) {
                    $balance->formatted_total     = wc_price( $balance->total / 100 );
                    $balance->formatted_available = wc_price( $balance->available / 100 );
                    $viewData['balance'] = $balance;

                    $this->extract_result_message ( $viewData );

                    $viewData["current_account_mode"] = $this->test_mode ? "TEST" : "LIVE";

                    Omise_Util::render_view( "includes/templates/omise-wp-admin-page.php", $viewData );

                    $this->register_dashboard_script();
                } else {
                    echo "<div class='wrap'><div class='error'>Unable to get the balance information. Please verify that your private key is valid. [" . esc_html( $balance->message ) . "]</div></div>";
                }
            } catch ( Exception $e ) {
                echo "<div class='wrap'><div class='error'>" . esc_html ( $e->getMessage() ) . "</div></div>";
            }
        }

        function extract_result_message( &$viewData ) {
            $viewData["message"] = isset( $_GET["omise_result_msg"] ) ? $_GET["omise_result_msg"] : "";
        }

        function register_dashboard_script() {
            wp_enqueue_script(
                "omise-dashboard-js",
                plugins_url( "/assets/javascripts/omise-dashboard-handler.js", __FILE__ ),
                array(
                    "jquery"
                ),
                OMISE_WOOCOMMERCE_PLUGIN_VERSION,
                true
            );
            wp_enqueue_style(
                "omise-css",
                plugins_url( "/assets/css/omise-css.css", __FILE__ ),
                array(),
                OMISE_WOOCOMMERCE_PLUGIN_VERSION
            );
        }
    }
}
