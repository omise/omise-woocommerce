<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Payload' ) ) {
	return;
}

abstract class Omise_FBBot_Payload {
	const GET_START_CLICKED = "GET_START_CLICKED";
	const FEATURE_PRODUCTS = "PAYLOAD_FEATURE_PRODUCTS";
	const PRODUCT_CATEGORY = "PAYLOAD_PRODUCT_CATEGORY";
	const CHECK_ORDER = "PAYLOAD_CHECK_ORDER";
	const HELP = "PAYLOAD_HELP";
}