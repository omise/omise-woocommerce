<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Chatbot_Payloads' ) ) {
	return;
}

class Omise_Chatbot_Payloads {
	const GET_START_TAPPED = 'GET_START_TAPPED';
}
