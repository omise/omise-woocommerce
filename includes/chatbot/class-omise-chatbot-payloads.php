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
	const ACTION_PRODUCT_CATEGORY  = 'ACTION_PRODUCT_CATEGORY';
	const ACTION_ORDER_STATUS      = 'ACTION_ORDER_STATUS';
}
