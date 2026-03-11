<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

abstract class Omise_Block_Payment extends AbstractPaymentMethodType {
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
     * Additional data required in the UI
     */
    protected $additional_data;

    /**
     * Returns the script asset metadata or a safe fallback when build artifacts are missing.
     *
     * @param string $asset_path Path to generated *.asset.php file.
     * @return array
     */
    private function load_script_asset( $asset_path ) {
        static $asset_cache = [];

        $defaults = [
            'dependencies' => [],
            'version'      => defined( 'OMISE_WOOCOMMERCE_PLUGIN_VERSION' ) ? OMISE_WOOCOMMERCE_PLUGIN_VERSION : null,
        ];

        if ( isset( $asset_cache[ $asset_path ] ) ) {
            return $asset_cache[ $asset_path ];
        }

        if ( ! file_exists( $asset_path ) ) {
            $asset_cache[ $asset_path ] = $defaults;
            return $asset_cache[ $asset_path ];
        }

        $asset = require $asset_path;
        if ( ! is_array( $asset ) ) {
            $asset_cache[ $asset_path ] = $defaults;
            return $asset_cache[ $asset_path ];
        }

        $asset_cache[ $asset_path ] = array_merge( $defaults, $asset );
        return $asset_cache[ $asset_path ];
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
        if (!wp_script_is("wc-{$this->name}-payments-blocks", 'enqueued')) {
            $script_asset = $this->load_script_asset( __DIR__ . "/../assets/js/build/{$this->name}.asset.php" );
            wp_enqueue_script(
                "wc-{$this->name}-payments-blocks",
                plugin_dir_url(__DIR__) . "assets/js/build/{$this->name}.js",
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );
        }

        return ["wc-{$this->name}-payments-blocks"];
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        if (!is_checkout()) {
            return [];
        }

        $this->set_additional_data();

        return [
            'name'        => $this->name,
            'title'       => $this->get_setting('title'),
            'description' => $this->get_setting('description'),
            'supports'    => array_filter($this->gateway->supports, [$this->gateway, 'supports']),
            'data'        => $this->additional_data,
            'is_active'   => $this->is_active(),
            'locale'      => get_locale(),
        ];
    }

    /**
     * Set additional data requried to make UI work. Different payment
     * methods may require different additional data.
     *
     * @return void
     */
    abstract public function set_additional_data();
}
