<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Component_Button_Url' ) ) {
	return;
}

/**
 * @see https://developers.facebook.com/docs/messenger-platform/send-messages/buttons/url
 */
class Omise_Chatbot_Component_Button_Url extends Omise_Chatbot_Component_Button {
	/**
	 * @param string $title
	 */
	public function __construct( $title ) {
		parent::__construct( 'web_url' );

		$this->set_title( $title );
	}

	/**
	 * @return array
	 */
	public function default_attributes() {
		return array(
			'url'                  => '',
			'title'                => '',
			'webview_height_ratio' => 'full'
		);
	}

	/**
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->set_attribute( 'title', $title );
	}

	/**
	 * @param string $url
	 */
	public function set_url( $url ) {
		$this->set_attribute( 'url', $url );
	}

	/**
	 * @param string $webview_height_ratio
	 */
	public function set_webview_height_ratio( $webview_height_ratio ) {
		$this->set_attribute( 'webview_height_ratio', $webview_height_ratio );
	}
}
