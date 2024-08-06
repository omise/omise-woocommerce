<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
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
		'Omise_Payment_FPX',
		'Omise_Payment_Creditcard',
		'Omise_Payment_Installment',
		'Omise_Payment_Internetbanking',
		'Omise_Payment_Konbini',
		'Omise_Payment_Mobilebanking',
		'Omise_Payment_Paynow',
		'Omise_Payment_Promptpay',
		'Omise_Payment_Truemoney',
		'Omise_Payment_Alipay_China',
		'Omise_Payment_Alipay_Hk',
		'Omise_Payment_Dana',
		'Omise_Payment_Gcash',
		'Omise_Payment_Kakaopay',
		'Omise_Payment_TouchNGo',
		'Omise_Payment_RabbitLinePay',
		'Omise_Payment_OCBC_Digital',
		'Omise_Payment_GrabPay',
		'Omise_Payment_GooglePay',
		'Omise_Payment_Boost',
		'Omise_Payment_ShopeePay',
		'Omise_Payment_Maybank_QR',
		'Omise_Payment_DuitNow_QR',
		'Omise_Payment_DuitNow_OBW',
		'Omise_Payment_Atome',
		'Omise_Payment_PayPay',
		'Omise_Payment_Wechat_Pay',
	);

	/**
	 * @param string $id  Omise payment method's id.
	 */
	public static function get_payment_method( $id ) {
		$gateway = WC_Payment_Gateways::instance();
		$methods = $gateway->payment_gateways();
		return isset( $methods[ $id ] ) ? $methods[ $id ] : null;
	}
}
