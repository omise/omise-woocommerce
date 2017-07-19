<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Endpoints' ) ) {
  return;
}

class Omise_FBBot_Endpoints extends WP_REST_Controller {

	private static $instance;

	/**
	 * @var string
	 */
	private $facebook_page_verify_token;

	private function __construct() {
		$this->facebook_page_verify_token = Omise_FBBot_Configurator::get_fb_settings('facebook_page_verify_token');
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function register_bot_api_routes() {
		$namespace = Omise_FBBot_Configurator::get_namespace();

		register_rest_route( $namespace, '/webhook', array(
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this, 'verify_fb_token_callback' ),
                'permission_callback' => array( $this, 'verify_fb_token_request' )
            ),

            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'handle_request')
            )
        ) 
    );

    register_rest_route( $namespace, '/omise_triggered', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'triggered_from_omise' )
            )
        ) 
    );

    register_rest_route( $namespace, '/messenger_checkout', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'messenger_checkout' )
            )
        ) 
    );

    register_rest_route( $namespace, '/checking_payment', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'checking_payment' )
            )
        ) 
    );
	}

	public function verify_fb_token_callback( $request ) {
    $params = $request->get_query_params();
    echo $params['hub_challenge'];
    die();
  }

  public function verify_fb_token_request( $request = NULL ) {
    $params = $request->get_query_params();

    if ( $params && isset( $params['hub_challenge'] ) && $params['hub_verify_token'] == $this->facebook_page_verify_token ) {
        return true;
    }

    return false;
  }

  public function handle_request ( $request ) {
		$params = $request->get_params();

    if ( ! ( $params && $params['entry'] ) ) {
      return;
    }

    foreach ( (array) $params['entry'] as $entry ) {
      if ( ! ( $entry && $entry['messaging'] ) ) {
        break;
      }

      foreach ( (array) $entry['messaging'] as $messaging_event ) {
        if ( isset( $messaging_event['message'] ) ) {
            if ( isset( $messaging_event['message']['is_echo'] ) ) {
                break;
            }

            if ( isset( $messaging_event['message']['quick_reply'] ) ) {
                break;
            }

            $sender_id = $messaging_event['sender']['id'];

            // Handle text message
            $text = $messaging_event['message']['text'];
            Omise_FBBot_Request_Handler::handle_message_from( $sender_id, $text );
            break;

        } else if ( isset( $messaging_event['postback'] ) ) {
            // Handle payload
            $sender_id = $messaging_event['sender']['id'];
            $payload = $messaging_event['postback']['payload'];

            Omise_FBBot_Request_Handler::handle_payload_from( $sender_id, $payload );
        } else {
          // Unused case
          break;
        }
      } // foreach ['messaging']

    } // foreach ['entry']
	}

  public function triggered_from_omise( $request ) {
  	$body = json_decode( $request->get_body() );

    if ( ( ! isset( $body ) ) || ( ! isset( $body->data ) ) ) 
      return;

  	$charge = $body->data;
    $metadata = $charge->metadata;
  
    if ( ! isset( $metadata ) || ! isset( $metadata->source ) ||  $metadata->source != 'woo_omise_bot' ) {
      // NOTE: Ignore from other source, allow only woocommerece bot
      return;
    }

    $event_name = $body->key;
    
    switch ( $event_name ) {
      case 'charge.create':
        // Charge has been created
        if ( $charge->return_uri ) {
          // Ignore case of 3ds enable for normal charge
          return;
        } 

        // Update order status here!
        $order_id = $metadata->order_id;
        Omise_Messenger_Bot_WooCommerce::update_order_status( $order_id, $charge );

        $sender_id = $charge->metadata->messenger_id;
        if( ! isset( $sender_id ) )
          return;

        $thanks_message = Omise_Messenger_Bot_Conversation_Generator::thanks_for_purchase_message( $order_id );
        
        $response = Omise_Messenger_Bot_Conversation_Handler::send_message_to( $sender_id, $thanks_message );
        break;

      case 'charge.complete':
        // Complete charge (only for 3D-Secure charge and Internet Banking)
        // Should query charge from omise api and check again : 'charge->paid' = 1 is success for make sure
        // $charge = OmiseCharge::retrieve( $id, '', $this->secret_key );
        // if ( ! OmisePluginHelperCharge::isPaid( $charge ) )
        // handle $charge['failure_message']
        // if success go to thanks page, if fail go to error page( or send success||fail message)

        // Update order status here!
        $order_id = $metadata->order_id;
        Omise_Messenger_Bot_WooCommerce::update_order_status( $order_id, $charge );

        // 
        $sender_id = $charge->metadata->messenger_id;
        if( ! isset( $sender_id ) )
          return;

        $thanks_message = Omise_Messenger_Bot_Conversation_Generator::thanks_for_purchase_message( $order_id );
        
        $response = Omise_Messenger_Bot_Conversation_Handler::send_message_to( $sender_id, $thanks_message );
        break;
      
      default:
        error_log( $event_name .' is not create or complete charge event we ignore this case.' );
        return false;
        break;
    }

    
  }

  public function messenger_checkout( $request ) {
    $params = $request->get_params();

    $payment_handler = Omise_Messenger_Bot_Payment_Handler::get_instance();
    $payment_handler->process_payment_by_bot( $params );
  }

}