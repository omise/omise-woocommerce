<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if (  class_exists( 'Omise_FBBot_Payment_Handler') ) {
  return;
}

class Omise_FBBot_Payment_Handler {		

	private static $instance;
  private $private_key, $public_key, $test_mode;

	private function __construct() {
    $settings = get_option( 'woocommerce_omise_settings', null );

    if ( is_null($settings) || ! is_array( $settings ) ) {
      return;
    }

    $this->test_mode = isset( $settings['sandbox'] ) && $settings['sandbox'] == 'yes';
    $this->private_key = $this->test_mode ? $settings['test_private_key'] : $settings['live_private_key'];
    $this->public_key  = $this->test_mode ? $settings['test_public_key'] : $settings['live_public_key'];

    if ( empty( $this->private_key ) || empty( $this->public_key ) ) {
      return;
    }

    // Set omise key
    $this->set_oms_key();

		function load_scripts() {
      $settings = get_option( 'woocommerce_omise_settings', null );
      $test_mode = isset( $settings['sandbox'] ) && $settings['sandbox'] == 'yes';
			$public_key = $test_mode ? $settings[ "test_public_key" ] : $settings[ "live_public_key" ];

      // Note : Set for testing on staging
			// wp_enqueue_script( 'omise-js', 'https://omise-cdn.s3.amazonaws.com/assets/frontend-libs/staging-omise.js', array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

      wp_enqueue_script( 'omise-js', 'https://cdn.omise.co/omise.js', array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
			
			wp_enqueue_script( 'omise-util', plugins_url( 'omise-woocommerce/assets/javascripts/omise-util.js' ), array( 'omise-js' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

			wp_enqueue_script( 'omise-payment-on-messenger-form-handler', plugins_url( 'omise-woocommerce/assets/javascripts/omise-payment-on-messenger-form-handler.js'), array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true);

			wp_localize_script( 'omise-payment-on-messenger-form-handler', 'omise_params', array(
				'key'       => $public_key,
				'vault_url' => OMISE_VAULT_HOST
			) );
		}

		add_action( 'wp_enqueue_scripts', 'load_scripts' );
	}

  function set_oms_key() {
    define( 'OMISE_PUBLIC_KEY', $this->public_key );
    define( 'OMISE_SECRET_KEY', $this->private_key );
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
		global $wp;
    global $wp_query;
		global $payment_page_detect;
    
    $payment_page_url = "pay-on-messenger";
    $payment_purchase_complete = "complete-payment";
    $payment_error_url = "pay-on-messenger-error";

    if ( strtolower( $wp->request ) == $payment_purchase_complete ) {
      
      if ( wp_redirect( 'https://www.messenger.com/closeWindow/?image_url=https://res.cloudinary.com/crunchbase-production/image/upload/v1496176559/uaapxq8gogk327z2efws.png&display_text=THANKS%20FOR%20PURCHASE' ) ) {
        exit;
      }

      return $posts;
    }

    // Create custom page
    $post = new stdClass;

    if ( strtolower( $wp->request ) == $payment_page_url ) {
      $post->post_title = __( 'Your order' );
      $post->post_content = $this->payment_page_render();
    }

    if ( strtolower( $wp->request ) == $payment_error_url ) {
      $post->post_title = __( 'System error' );
      $post->post_content = $this->payment_error_page_render();
    }

    $post->post_author = 1;
    $post->post_name = strtolower( $wp->request );
    $post->guid = get_bloginfo( 'wpurl' ) . '/' . strtolower( $wp->request );
    $post->ID = -1;
    $post->post_type = 'page';
    $post->post_status = 'static';
    $post->comment_status = 'closed';
    $post->ping_status = 'open';
    $post->comment_count = 0;
    $post->post_date = current_time( 'mysql' );
    $post->post_date_gmt = current_time( 'mysql', 1 );
    $posts = NULL;
    $posts[] = $post;
    
    // make wpQuery believe this is a real page too
    $wp_query->is_page = true;
    $wp_query->is_singular = true;
    $wp_query->is_home = false;
    $wp_query->is_archive = false;
    $wp_query->is_category = false;
    unset( $wp_query->query["error"] );
    $wp_query->query_vars["error"] = "";
    $wp_query->is_404 = false;

    return $posts;
	}

	private function payment_page_render () {
		global $wp_query;

  	if ( ! isset( $wp_query->query_vars['product_id'] ) || ! isset( $wp_query->query_vars['messenger_id'] ) ) {
  		return '<strong>404 Your order not found</strong>';
  	}

  	$product_id = $wp_query->query_vars['product_id'];
  	$messenger_id = $wp_query->query_vars['messenger_id'];

    $url = plugin_dir_path(dirname(__DIR__)) . 'templates/fbbot/payment-form.php';
    ob_start();
    include( $url );
    return ob_get_clean();
	}

  private function payment_error_page_render () {
    global $wp_query;

    if ( ! isset( $wp_query->query_vars['error_message'] ) ) {
      return '<strong>Woocommerce system has error. Please try again.</strong>';
    }

    $error_message = $wp_query->query_vars['error_message'];

    return '<strong>' . $error_message . '</strong>';
  }

  public function process_payment_by_bot( $params ) {
    /* rearrange step
      1. receive post data from payment page
      2. Create wc order
      3. Create charge 
      4. update order status
    */

    $omise_token = $params['omise_token'];
    $product_id = $params['product_id'];
    $messenger_id = $params['messenger_id'];

    $settings = get_option( 'woocommerce_omise_settings', null );

    $order = Omise_FBBot_WooCommerce::create_order( $product_id, $messenger_id );

    $metadata = array(
      'source' => 'woo_omise_bot',
      'product_id' => $product_id,
      'messenger_id' => $messenger_id,
      'order_id' => $order->get_order_number()
    );

    $product = Omise_FBBot_WCProduct::create( $product_id );
    $amount = 0;
    $price_for_sale = $product->price;

    if ( 'THB' === strtoupper( $product->currency ) ) {
      $amount = $price_for_sale * 100;
    } else {
      $amount = $price_for_sale;
    }
                    
    $data = array(
      'amount' => $amount,
      'currency' => $product->currency,
      'description' => 'Order from Messenger app. ProductID is '.$product_id.' and CustomerID is '.$messenger_id,
      'metadata' => $metadata,
      'card' => $omise_token
    );

    $support_3dsecure = isset( $settings['omise_3ds'] ) && $settings['omise_3ds'] === 'yes';

    if ( $support_3dsecure ) {
      $return_uri =  site_url() . '/complete-payment';

      $data['return_uri'] = $return_uri;
    }

    // Create Charge
    try {
      $charge = OmiseCharge::create( $data );
      // Just sent message to user for let them know we received these order
      $prepare_confirm_message = Omise_FBBot_Conversation_Generator::prepare_confirm_order_message();
      $response = Omise_FBBot_HTTPService::send_message_to( $messenger_id, $prepare_confirm_message );

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
      error_log("catch error : " . $e->getMessage());

      $error_message = str_replace(" ", "%20", $e->getMessage());

      // [WIP] - Redirect to error page
      $redirect_uri =  site_url() . '/pay-on-messenger-error/?error_message=' . $error_message;
      if ( wp_redirect( $redirect_uri ) ) {
          exit;
      }
    }
  }

}
