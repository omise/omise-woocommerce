<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Payloads' ) ) {
	return;
}

class Omise_Chatbot_Payloads {
	/**
	 * @var string
	 */
	const GET_START_TAPPED          = 'GET_START_TAPPED';
	const ACTION_CHECK_ORDER_STATUS = 'ACTION_CHECK_ORDER_STATUS';
	const ACTION_HELP               = 'ACTION_HELP';
}
