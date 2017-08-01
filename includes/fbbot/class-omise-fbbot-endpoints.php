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
                'callback' => array($this, 'callback_fb_webhook')
            )
        ) 
    );

    register_rest_route( $namespace, '/callback_omise_webhook', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'callback_omise_webhook' )
            )
        ) 
    );

    register_rest_route( $namespace, '/callback_fbbot_checkout', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'callback_fbbot_checkout' )
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

  public function callback_fb_webhook ( $request ) {
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

  public function callback_omise_webhook( $request ) {
    Omise_FBBot_Request_Handler::handle_callback_omise_webhook( $request );
  }

  public function callback_fbbot_checkout( $request ) {
    $params = $request->get_params();
    $payment_handler = Omise_FBBot_Payment_Handler::get_instance();
    $payment_handler->process_payment_by_bot( $params );
  }

}