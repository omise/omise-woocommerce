<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class Omise_Block_Credit_Card extends AbstractPaymentMethodType {
    /**
     * The gateway instance.
     *
     * @var Omise_Block_Credit_Card
     */
    private $gateway;

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise';

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
        if ($this->is_active()) {
            $script_asset_path =  __DIR__ .  '/../assets/js/build/credit_card.asset.php';
            $script_asset = file_exists( $script_asset_path )
                ? require $script_asset_path
                : [
                    'dependencies' => [],
                    'version' => '1.0.0'
                ];

            // Load the script related to OmiseJS in checkout
            if (is_checkout()) {
                wp_enqueue_script(
                    'embedded-js',
                    plugins_url( '../../assets/javascripts/omise-embedded-card.js', __FILE__ ),
                    ['omise-js'],
                    OMISE_WOOCOMMERCE_PLUGIN_VERSION,
                    true
                );

                $script_asset['dependencies'] = array_merge($script_asset['dependencies'], ['embedded-js']);
            }

            // Load the script for UI in checkout and cart page.
            if (is_checkout() || is_cart() || is_page('cart')) {
                wp_enqueue_script(
                    "{$this->name}-payments-blocks",
                    plugin_dir_url( __DIR__ ) . 'assets/js/build/credit_card.js',
                    $script_asset[ 'dependencies' ],
                    $script_asset[ 'version' ],
                    true
                );
            }
        }

        return [ "{$this->name}-payments-blocks" ];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        $viewData = $this->gateway->get_existing_cards();
        $viewData = array_merge($viewData, $this->gateway->get_secure_form_config());

        return array_merge($viewData, [
            'name'        => $this->name,
            'title'       => $this->get_setting( 'title' ),
            'description' => $this->get_setting( 'description' ),
            'features'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
            'locale'      => get_locale(),
            'public_key'  => Omise_Setting::instance()->public_key(),
            'is_active'   => $this->is_active(),
        ]);
    }
}
