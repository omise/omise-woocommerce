<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBot_Page_Setup' ) ) {
    return;
}

class Omise_FBot_Page_Setup {

    private static $instance;
    private $facebook_page_access_token;

    private function __construct() {
        $this->facebook_page_access_token = Omise_FBBot_Configurator::get_fb_settings( 'facebook_page_access_token' );
    }

    public static function get_instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function set_page_get_started_button() {
        $data = array(
            'get_started' => array( 'payload' => Omise_FBBot_Payload::GET_START_CLICKED )
        );

        $url = Omise_FBBot_Configurator::get_fb_profile_endpoint();

        $response = Omise_FBBot_HTTPService::send_request( $url, $data );

        $body = json_decode( $response['body'] );
        if ( isset( $body->error ) ) {
            error_log( print_r( $body->error, true ) );
        } else {
            error_log("Update : set_page_get_started_button success");
        }
    }

    public function set_page_greeting_message() {
        $blog_title = get_bloginfo('name');

        $data = array(
            array(
              'locale' => 'default',
              'text' => "Hi {{user_first_name}}, Welcome to " . $blog_title . "\r\nYou can . . . \r\n- Shopping feature and cool products\r\n- Check your order status \r\n- and more :D"
            )
          );

        $url = Omise_FBBot_Configurator::get_fb_profile_endpoint();

        $response = Omise_FBBot_HTTPService::send_request( $url, $data );

        $body = json_decode( $response['body'] );
        if ( isset( $body->error ) ) {
            error_log( print_r( $body->error, true ) );
        } else {
            error_log("Update : set_page_greeting_message success");
        }
    }

    public function set_page_persistent_menu() {
        // clear old persistent menu
        $delete_persistent_menu_data = array(
            'fields' => array(
                'persistent_menu'
              )
          );

        $url = Omise_FBBot_Configurator::get_fb_profile_endpoint();

        $deleteResponse = Omise_FBBot_HTTPService::send_delete_request( $url, $delete_persistent_menu_data );
        $body = json_decode( $deleteResponse['body'] );
        if ( isset( $body->error ) ) {
            error_log( print_r( $body->error, true ) );
        } else {
            error_log("Clear : old persistent_menu success");
        }

        // Update new persistent menu
        $persistent_menu_data = array(
        'persistent_menu' => array(
            array(
            'locale' => 'default',
            'composer_input_disabled' => false,
            'call_to_actions' => array(
                array(
                    'type' => 'postback',
                    'title' => 'Check order status',
                    'payload' => Omise_FBBot_Payload::CHECK_ORDER
                    ),

                array(
                    'type' => 'web_url',
                    'title' => 'View Website',
                    'url' => site_url()
                    ),

                array(
                    'type' => 'postback',
                    'title' => 'Help',
                    'payload' => Omise_FBBot_Payload::HELP
                    )
                
                )
            )
          )
        );

        $response = Omise_FBBot_HTTPService::send_request( $url, $persistent_menu_data );
        $body = json_decode( $response['body'] );

        if ( isset( $body->error ) ) {
            error_log( print_r( $body->error, true ) );
        } else {
            error_log("Update : setup persistent_menu success");
        }

    }

    public function set_page_whitelist_domain() {
        $data = array(
            'whitelisted_domains' => array( site_url() )
            );

        $url = Omise_FBBot_Configurator::get_fb_profile_endpoint();

        $response = Omise_FBBot_HTTPService::send_request( $url, $data );

        $body = json_decode( $response['body'] );
        if ( isset( $body->error ) ) {
            error_log( print_r( $body->error, true ) );
        } else {
            error_log("Update : whitelist_domain success");
        }
    }

    public function facebook_page_setup() {
        if ( ! $this->facebook_page_access_token ) {
            error_log( 'Facebook page access token is empty, bot will setup page later' );
            return;
        }

        self::$instance->set_page_get_started_button();
        self::$instance->set_page_greeting_message();
        self::$instance->set_page_persistent_menu();
        self::$instance->set_page_whitelist_domain();
    }
}
