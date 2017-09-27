<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Payloads' ) ) {
	return;
}

class Omise_Chatbot_Payloads {
	/**
	 * @var string
	 */
	const GET_START_TAPPED         = 'GET_START_TAPPED';
	const ACTION_FEATURED_PRODUCTS = 'ACTION_FEATURED_PRODUCTS';
	const ACTION_PRODUCT_GALLERY   = 'ACTION_PRODUCT_GALLERY';
	const ACTION_PRODUCT_CATEGORY  = 'ACTION_PRODUCT_CATEGORY';
	const ACTION_PRODUCT_LIST      = 'ACTION_PRODUCT_LIST';
	const ACTION_ORDER_STATUS      = 'ACTION_ORDER_STATUS';

	/**
	 * @var array
	 */
	protected $payload;

	/**
	 * @param string $event
	 */
	public function __construct( $event ) {
		$this->payload = array(
			'object' => 'omise',
			'event'  => $event,
			'data'   => array()
		);
	}

	/**
	 * @param  string $event
	 *
	 * @return Omise_Chatbot_Payloads
	 */
	public static function create( $event ) {
		return new self( $event );
	}

	/**
	 * @param array $data
	 */
	public function set_data( $data ) {
		$this->payload['data'] = array_merge( $this->payload['data'], $data );
	}

	/**
	 * @return string  of JSON payload
	 */
	public function to_json() {
		return json_encode( $this->payload );
	}
}
