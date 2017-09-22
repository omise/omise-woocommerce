<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot' ) ) {
	return;
}

class Omise_Chatbot extends Omise_Setting {
	/**
	 * @var string
	 */
	const FACEBOOK_BASE_ENDPOINT    = 'https://graph.facebook.com/v2.6/me';
	const FACEBOOK_PROFILE_ENDPOINT = 'messenger_profile';
	const FACEBOOK_MESSAGE_ENDPOINT = 'messages';

	/**
	 * @return string
	 *
	 * @since  3.2
	 */
	protected function get_facebook_endpoint( $endpoint ) {
		return esc_url(
			add_query_arg(
				array( 'access_token' => $this->get_setting( 'chatbot_facebook_page_access_token' ) ),
				self::FACEBOOK_BASE_ENDPOINT . '/' . $endpoint
			)
		);
	}

	/**
	 * @return string
	 *
	 * @since  3.2
	 */
	public function get_facebook_profile_endpoint() {
		return $this->get_facebook_endpoint( self::FACEBOOK_PROFILE_ENDPOINT );
	}

	/**
	 * @return string
	 *
	 * @since  3.2
	 */
	public function get_facebook_message_endpoint() {
		return $this->get_facebook_endpoint( self::FACEBOOK_MESSAGE_ENDPOINT );
	}

	/**
	 * Setup Facebook Messenger bot.
	 *
	 * @see   https://developers.facebook.com/docs/messenger-platform/reference/messenger-profile-api
	 *
	 * @since 3.2
	 */
	public function setup() {
		if ( 'yes' === $this->get_setting( 'chatbot_enabled' ) ) {
			wp_safe_remote_post(
				$this->get_facebook_profile_endpoint(),
				array(
					'timeout' => 60,
					'body'    => array(
						'get_started' => array(
							'payload' => Omise_Chatbot_Payloads::GET_START_TAPPED
						),
						'greeting' => array(
							array(
								'locale' => 'default',
								'text'   => "Hi {{user_first_name}}, welcome to " . get_bloginfo( 'name' )
							)
						)
					)
				)
			);
		}
	}
}
