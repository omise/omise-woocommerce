<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class Omise_Block_Mobile_Banking extends AbstractPaymentMethodType {
    /**
     * The gateway instance.
     */
    protected $gateway;

    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_mobilebanking';

    /**
     * Returns the script asset metadata or a safe fallback when build artifacts are missing.
     *
     * @param string $asset_path Path to generated *.asset.php file.
     * @return array
     */
    private function load_script_asset( $asset_path ) {
        $defaults = [
            'dependencies' => [],
            'version'      => defined( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION' ) ? OMISE_WOOCOMMERCE_PLUGIN_VERSION : null,
        ];

        if ( ! file_exists( $asset_path ) ) {
            return $defaults;
        }

        $asset = require_once $asset_path;
        if ( ! is_array( $asset ) ) {
            $asset = include_once $asset_path;
        }

        if ( ! is_array( $asset ) ) {
            return $defaults;
        }

        return array_merge( $defaults, $asset );
    }

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option("woocommerce_{$this->name}_settings", []);
        $gateways       = WC()->payment_gateways->payment_gateways();
        $this->gateway  = $gateways[$this->name];
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
        if (!wp_script_is('wc-omise-mobilebanking-payments-blocks', 'enqueued')) {
            $script_asset = $this->load_script_asset( __DIR__ . '/../assets/js/build/omise-mobilebanking.asset.php' );
            wp_register_script(
                "wc-omise-mobilebanking-payments-blocks",
                plugin_dir_url(__DIR__) . 'assets/js/build/omise-mobilebanking.js',
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );

            wp_enqueue_script('wc-omise-mobilebanking-payments-blocks');
        }

        return ['wc-omise-mobilebanking-payments-blocks'];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        $currency = get_woocommerce_currency();
        $is_upa_enabled = false;

        if ( class_exists( 'Omise_Setting' ) && method_exists( 'Omise_Setting', 'instance' ) ) {
            $is_upa_enabled = Omise_Setting::instance()->is_upa_enabled();
        }

        return [
            'name'        => $this->name,
            'title'       => $this->get_setting('title'),
            'description' => $this->get_setting('description'),
            'supports'    => array_filter($this->gateway->supports, [$this->gateway, 'supports']),
            'data' => [
                'backends' => $is_upa_enabled ? [] : $this->gateway->backend->get_available_providers($currency),
                'is_upa_enabled' => (bool) $is_upa_enabled,
            ],
            'is_active'   => $this->is_active(),
        ];
    }
}
