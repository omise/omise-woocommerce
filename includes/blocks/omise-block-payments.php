<?php

class Omise_Block_Payments {

    private $container;

    private $payment_methods = [];

    function __construct($container) {
        $this->container = $container;
        $this->add_payment_methods();
        $this->initialize();
    }

    private function add_payment_methods() {
        $this->container->register(Omise_Block_Credit_Card::class, function ( $container ) {
			// return new Omise_Block_Credit_Card( $container->get( AssetsApi::class ) );
            return new Omise_Block_Credit_Card();
		} );

        $this->container->register(Omise_Block_Promptpay::class, function ( $container ) {
			// return new Omise_Block_Credit_Card( $container->get( AssetsApi::class ) );
            return new Omise_Block_Promptpay();
		} );
    }

    private function initialize() {
		add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_payment_methods' ) );
    }

    public function register_payment_methods( $registry ) {
		$payment_methods = [
            Omise_Block_Credit_Card::class,
            Omise_Block_Promptpay::class,
        ];

		foreach ( $payment_methods as $clazz ) {
			$registry->register( $this->container->get($clazz) );
		}
    }

    /**
	 * @return \PaymentPlugins\Blocks\Stripe\Payments\AbstractStripePayment[]
	 */
	// public function get_payment_methods() {
	// 	return $this->payment_methods;
	// }
}
