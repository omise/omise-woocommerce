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
        // Omise_Block_Touch_N_Go::class,
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
