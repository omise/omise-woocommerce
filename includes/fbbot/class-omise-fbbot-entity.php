<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( class_exists( 'Omise_FBBot_Entity' ) ) {
	return;
}

abstract class Omise_FBBot_Entity {
	const CONFIDENCE_RATE = 0.85;

	const GREETINGS = "greetings";
	const USER_GREETINGS = "user_greetings";
	const CHECK_ORDER_STATUS = "check_order_status";
	const NEED_HELP = "need_help";
	const CALL_SHOP_OWNER = "call_shop_owner";
}