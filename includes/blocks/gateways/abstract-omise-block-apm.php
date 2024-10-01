<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

abstract class Omise_Block_Apm extends AbstractPaymentMethodType {
    /**
     * The gateway instance.
     */
    protected $gateway;

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name;

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
        return $this->gateway->is_available();
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        if ( ! wp_script_is( 'wc-omise-one-click-apms-payments-blocks', 'enqueued' ) ) {
            $script_asset = require __DIR__ . '/../assets/js/build/omise-one-click-apms.asset.php';
            wp_enqueue_script(
                "wc-omise-one-click-apms-payments-blocks",
                plugin_dir_url( __DIR__ ) . 'assets/js/build/omise-one-click-apms.js',
                $script_asset[ 'dependencies' ],
                $script_asset[ 'version' ],
                true
            );
        }

        return [ 'wc-omise-one-click-apms-payments-blocks' ];
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
            'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
            'is_active'   => $this->is_active(),
        ];
    }
}
