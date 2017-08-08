<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

function register_omise_fbbot() {
	require_once dirname( __FILE__ ) . '/class-omise-payment.php';

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'Omise_Payment_FBBot' ) ) {
		return;
	}

	class Omise_Payment_FBBot extends Omise_Payment {
		private static $instance;
		
		public function __construct() {
			parent::__construct();

			$this->payment_page_url = "pay-on-messenger";
			$this->payment_purchase_complete_url = "complete-payment";
			$this->payment_error_url = "pay-on-messenger-error";
			$this->omise_3ds = $this->get_option( 'omise_3ds', false ) == 'yes';

			add_action( 'wp_enqueue_scripts', array( $this, 'omise_assets' ) );

			add_filter( 'the_posts', array( $this, 'payment_page_detect' ) );
			add_filter( 'query_vars', array( $this, 'parameter_queryvars' ) );
		}

		public function omise_assets() {
			wp_enqueue_style( 'omise-css', plugins_url( '../../assets/css/omise-css.css', __FILE__ ), array(), OMISE_WOOCOMMERCE_PLUGIN_VERSION );

			wp_enqueue_script( 'omise-js', 'https://cdn.omise.co/omise.js', array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

			wp_enqueue_script( 'omise-util', plugins_url( '../../assets/javascripts/omise-util.js', __FILE__ ), array( 'omise-js' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

			wp_enqueue_script( 'omise-payment-on-messenger-form-handler', plugins_url( '../../assets/javascripts/omise-payment-on-messenger-form-handler.js', __FILE__ ), array( 'omise-js', 'omise-util' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

			wp_localize_script( 'omise-payment-on-messenger-form-handler', 'omise_params', array(
					'key' => $this->public_key()
				) 
			);
		}

		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function parameter_queryvars( $qvars ) {
			$qvars[] = 'product_id';
			$qvars[] = 'messenger_id';
			$qvars[] = 'error_message';

			return $qvars;
		}

		public function payment_page_detect( $posts ) {
			if ( $this->is_omise_purchase_complete_page() ) {
				$image_url = urlencode( site_url() . '/wp-content/plugins/omise-woocommerce/assets/images/omise_logo.png' );
				$display_text = urlencode( 'THANKS FOR PURCHASE' );
				$url = 'https://www.messenger.com/closeWindow/?image_url=' . $image_url . '&display_text=' . $display_text;
				if ( wp_redirect( $url ) ) {
					exit;
				}

				return $posts;
			}

			if ( $this->is_omise_payment_page() ) {
				$posts = array( $this->build_payment_page() );
			} else if ( $this->is_omise_payment_error_page() ) {
				$posts = array( $this->build_payment_error_page() );
			}

			return $posts;
		}

		private function init_page() {
			global $wp;
			global $wp_query;

			$post = new stdClass;

			$post->post_author 				= 1;
			$post->post_name 				= strtolower( $wp->request );
			$post->guid 					= get_bloginfo( 'wpurl' ) . '/' . strtolower( $wp->request );
			$post->ID 						= -1;
			$post->post_type 				= 'page';
			$post->post_status 				= 'static';
			$post->comment_status 			= 'closed';
			$post->ping_status 				= 'open';
			$post->comment_count 			= 0;
			$post->post_date 				= current_time( 'mysql' );
			$post->post_date_gmt 			= current_time( 'mysql', 1 );

			$wp_query->is_page 				= true;
			$wp_query->is_singular 			= true;
			$wp_query->is_home 				= false;
			$wp_query->is_archive 			= false;
			$wp_query->is_category 			= false;
			$wp_query->query_vars["error"] 	= '';
			$wp_query->is_404 				= false;

			return $post;
		}

		private function build_payment_page() {
			$post = $this->init_page();
			$post->post_title = __( 'Your order', 'omise' );
			$post->post_content = $this->payment_page_render();

			return $post;
		}

		private function build_payment_error_page() {
			$post = $this->init_page();
			$post->post_title = __( 'System error', 'omise' );
			$post->post_content = $this->payment_error_page_render();

			return $post;
		}

		private function payment_page_render() {
			global $wp_query;

			if ( ! isset( $wp_query->query_vars['product_id'] ) || ! isset( $wp_query->query_vars['messenger_id'] ) ) {
				return '<strong>404 Your order not found</strong>';
			}

			$product_id = $wp_query->query_vars['product_id'];
			$messenger_id = $wp_query->query_vars['messenger_id'];

			$url = OMISE_WOOCOMMERCE_PLUGIN_PATH . '/templates/fbbot/payment-form.php';
			ob_start();
			include( $url );
			return ob_get_clean();
		}

		private function payment_error_page_render() {
			global $wp_query;

			if ( ! isset( $wp_query->query_vars['error_message'] ) ) {
			  return '<strong>Woocommerce system has error. Please try again.</strong>';
			}

			$error_message = $wp_query->query_vars['error_message'];

			return '<strong>' . $error_message . '</strong>';
		}

		public function process_payment_by_bot( $params, $order ) {
			$charge_params = array(
				'amount'		=> $this->format_amount_subunit( $order->get_total(), $order->get_currency() ),
				'currency'    	=> $order->get_currency(),
				'description' 	=> 'OrderID is ' . $order->get_order_number() . ' : This order created from Omise FBBot and CustomerID is ' . $params['messenger_id'],
				'metadata' 		=> $params['metadata'],
				'card' 			=> $params['omise_token']
			);

			if ( $this->omise_3ds ) {
				$return_uri =  site_url() . '/complete-payment';

				$charge_params['return_uri'] = $return_uri;
			}

			// Create Charge
			try {
				$charge = OmiseCharge::create( $charge_params, '', $this->secret_key() );
				// We move checking charge status to request handler in handle triggered from omise method

				// Just sent message to user for let them know we received these order
				$prepare_confirm_message = Omise_FBBot_Conversation_Generator::prepare_confirm_order_message( $order->get_order_number() );
				$response = Omise_FBBot_HTTPService::send_message_to( $params['messenger_id'], $prepare_confirm_message );

				// If merchant enable 3ds mode
				if ( isset ( $charge['authorize_uri'] ) ) {
					if ( wp_redirect( $charge['authorize_uri'] ) ) {
						error_log( 'redirect to -> '. $charge['authorize_uri'] );
						exit;
					}
				
					return;
				}

				// If merchant disable 3ds mode : normal mode
				$redirect_uri =  site_url() . '/complete-payment';
				if ( wp_redirect( $redirect_uri ) ) {
					exit;
				}

			} catch (Exception $e) {
				$error_message = str_replace(" ", "%20", $e->getMessage());

				// Redirect to error page
				$redirect_uri =  site_url() . '/pay-on-messenger-error/?error_message=' . $error_message;
				if ( wp_redirect( $redirect_uri ) ) {
					exit;
				}
			}
		}

		private function is_omise_payment_page() {
			global $wp;

			return ( strtolower( $wp->request ) == $this->payment_page_url );
		}

		private function is_omise_payment_error_page() {
			global $wp;
			
			return ( strtolower( $wp->request ) == $this->payment_error_url );
		}

		private function is_omise_purchase_complete_page() {
			global $wp;
			
			return ( strtolower( $wp->request ) == $this->payment_purchase_complete_url );
		}

		private function is_accessible() {
			global $wp;

			return ( $this->is_omise_payment_page() || $this->is_omise_payment_error_page() );
		}
	}

	// Initial
	Omise_Payment_FBBot::get_instance();
}