<?php

class Omise_Block_Payments {

    private $container;

    private $payment_methods = [
        Omise_Block_Credit_Card::class,
        Omise_Block_Promptpay::class,
        Omise_Block_Alipay::class,
        Omise_Block_Alipay_HK::class,
        Omise_Block_Alipay_CN::class,
        Omise_Block_Gcash::class,
        Omise_Block_Kakaopay::class,
        Omise_Block_Dana::class,
        Omise_Block_Touch_N_Go::class,
        Omise_Block_Bill_Payment_Lotus::class,
        Omise_Block_Shopeepay::class,
        Omise_Block_Wechat_Pay::class,
        Omise_Block_Grabpay::class,
        Omise_Block_Paynow::class,
        Omise_Block_Ocbc_Digital::class,
        Omise_Block_Boost::class,
        Omise_Block_Maybank_QR::class,
        Omise_Block_DuitNow_QR::class,
        Omise_Block_Paypay::class,
        Omise_Block_RabbitLinePay::class,
        Omise_Block_Mobile_Banking::class,
        Omise_Block_Installment::class,
        Omise_Block_Fpx::class,
        Omise_Block_Atome::class,
        Omise_Block_Truemoney::class,
        Omise_Block_GooglePay::class,
    ];

    function __construct($container) {
        $this->container = $container;
        $this->add_payment_methods();
        $this->initialize();
    }

    private function add_payment_methods() {
        foreach($this->payment_methods as $payment_method) {
            $this->container->register($payment_method, function ( $container ) use ($payment_method) {
                return new $payment_method;
            } );
        }
    }

    private function initialize() {
		add_action( 'woocommerce_blocks_payment_method_type_registration', [ $this, 'register_payment_methods' ] );
    }

    public function register_payment_methods( $registry ) {
		foreach ( $this->payment_methods as $clazz ) {
			$registry->register( new $clazz );
		}
    }
}
