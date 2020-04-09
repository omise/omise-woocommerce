<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since v4.0
 */
class Omise_Payment_Factory {
	/**
	 * All the available payment methods
	 * that Omise WooCommerce supported.
	 *
	 * @var array
	 */
	public static $payment_methods = array(
		'Omise_Payment_Alipay',
		'Omise_Payment_Billpayment_Tesco',
		'Omise_Payment_Creditcard',
		'Omise_Payment_Installment',
		'Omise_Payment_Internetbanking',
		'Omise_Payment_Paynow',
		'Omise_Payment_Truemoney'
	);

	public static function get_payment_method( $id ) {
		$methods = ( WC_Payment_Gateways::instance() )->payment_gateways();
		return isset( $methods[ $id ] ) ? $methods[ $id ] : null;
	}
}
