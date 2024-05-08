<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class Omise_Block_Promptpay extends AbstractPaymentMethodType {
    /**
	 * The gateway instance.
	 *
	 * @var Omise_Block_Promptpay
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'omise_promptpay';

	public function __construct() {
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', [ $this, 'add_payment_request_order_meta' ], 8, 2 );
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( "woocommerce_{$this->name}_settings", [] );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return wc_string_to_bool( $this->get_setting( 'enabled', 'no' ) );
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_asset = require __DIR__ . '/../assets/js/build/promptpay.asset.php';
		wp_register_script(
			"{$this->name}-payments-blocks",
			plugin_dir_url( __DIR__ ) . 'assets/js/build/promptpay.js',
			$script_asset[ 'dependencies' ],
			$script_asset[ 'version' ],
			true
		);

		return [ "{$this->name}-payments-blocks" ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'name'        => $this->name,
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'features'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}

	/**
	 * Add payment request data to the order meta as hooked on the
	 * woocommerce_rest_checkout_process_payment_with_context action.
	 *
	 * @param PaymentContext $context Holds context for the payment.
	 * @param PaymentResult  $result  Result object for the payment.
	 */
	public function add_payment_request_order_meta( $context, &$result ) {
		$data = $context->payment_data;
		if ( ! empty( $data['payment_request_type'] ) && 'stripe' === $context->payment_method ) {
			$this->add_order_meta( $context->order, $data['payment_request_type'] );
		}
	}
}
