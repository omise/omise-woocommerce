<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if( ! class_exists( 'Omise_Messenger_Bot_Page_Setup' ) ) {
	class Omise_Messenger_Bot_Page_Setup {

		private static $instance;
		private $facebook_page_access_token;
		private $thread_api_url;

		private function __construct() {
			$settings = Omise_Util::get_settings();
      if ( ! isset( $settings ) )
        return;

			if ( ! isset( $settings['facebook_page_access_token'] ) )
				return;

			$this->facebook_page_access_token = $settings['facebook_page_access_token'];
      $this->thread_api_url = 'https://graph.facebook.com/v2.6/me/thread_settings?access_token=' . $this->facebook_page_access_token;
			$this->messenger_profile_url = 'https://graph.facebook.com/v2.6/me/messenger_profile?access_token=' . $this->facebook_page_access_token;
		}

		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function set_page_get_stated_button() {
      $body = array(
          'get_started' => array( 'payload' => 'GET_START_CLICKED' )
          );

      $data = array(
          'timeout' => 60,
          'body' => $body
      );

      $response = wp_remote_post( $this->messenger_profile_url, $data );
      $body = json_decode( $response['body'] );
      if ( isset( $body->error ) ) {
        error_log( print_r( $body->error, true ) );
      } else {
        error_log("update : set_page_get_stated_button success");
        error_log( print_r($body, true));
      }
		}

		public function set_page_greeting_message() {
			$blog_title = get_bloginfo('name');

      $body = array(
          'setting_type' => 'greeting', 
          'greeting' => array( 'text' => "Hi {{user_first_name}}, Welcome to ".$blog_title."\r\nYou can . . . \r\n- Shopping feature and cool products\r\n- Check your order status \r\n- and more :D" )
          );

      $data = array(
          'timeout' => 60,
          'body' => $body
      );

      $response = wp_remote_post( $this->thread_api_url, $data );
      $body = json_decode( $response['body'] );
      if ( isset( $body->error ) ) {
        error_log( print_r( $body->error, true ) );
      } else {
        error_log("update : set_page_greeting_message success");
        error_log( print_r($body, true));
      }
		}

    function wp_remote_delete($url, $args) {
      $defaults = array('method' => 'DELETE');
      $r = wp_parse_args( $args, $defaults );
      return wp_remote_request($url, $r);
    }

		public function set_page_persistent_menu() {
      
			$body = array(
      'persistent_menu' => array(
          array(
          'locale' => 'default',
          'composer_input_disabled' => false,
          'call_to_actions' => array(
              array(
                  'type' => 'postback',
                  'title' => 'Check order status',
                  'payload' => 'PAYLOAD_CHECK_ORDER'
                  ),

              array(
                  'type' => 'web_url',
                  'title' => 'View Website',
                  'url' => site_url()
                  ),

              array(
                  'type' => 'postback',
                  'title' => 'Help',
                  'payload' => 'PAYLOAD_HELP'
                  )
              
              )
          )
        )
      );
      

      $data = array(
          'timeout' => 60,
          'body' => $body
      );

      $deleteBody = array(
          'fields' => array(
              'persistent_menu'
            )
        );

      // clear old persistent menu
      $deleteResponse = self::$instance->wp_remote_delete( $this->messenger_profile_url, $deleteBody);
      $reponsebody = json_decode( $deleteResponse['body'] );

      if ( isset( $reponsebody->error ) ) {
        error_log( print_r( $reponsebody->error, true ) );
      } else {
        error_log("clear : persistent_menu success");
        error_log( print_r($reponsebody, true));
      }

      // update new persistent menu
      $response = wp_remote_post( $this->messenger_profile_url, $data );
      $reponsebody = json_decode( $response['body'] );

      if ( isset( $reponsebody->error ) ) {
        error_log( print_r( $reponsebody->error, true ) );
      } else {
        error_log("update : persistent_menu success");
        error_log( print_r($reponsebody, true));
      }

		}

		public function facebook_page_setup() {
			if ( ! $this->facebook_page_access_token ) {
				error_log( 'Facebook page access token is empty, bot will setup page later' );
				return;
			}

      self::$instance->set_page_greeting_message();
			self::$instance->set_page_get_stated_button();
			self::$instance->set_page_persistent_menu();
		}
	}
}