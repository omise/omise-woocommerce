<?php
defined ( 'ABSPATH' ) or die ( "No direct script access allowed." );

function register_omise_wc_gateway_plugin() {
	// prevent running directly without wooCommerce
	if (! class_exists ( 'WC_Payment_Gateway' ))
		return;
	
	if (! class_exists ( 'WC_Gateway_Omise' )) {
		require_once('class-omise-wc-gateway.php');
	}

	function add_omise_gateway($methods) {
		$methods [] = 'WC_Gateway_Omise';
		return $methods;
	}
	
	add_filter ( 'woocommerce_payment_gateways', 'add_omise_gateway' );
}

?>
