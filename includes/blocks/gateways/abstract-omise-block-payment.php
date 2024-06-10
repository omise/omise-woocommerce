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
            $script_asset = require_once __DIR__ . "/../assets/js/build/{$this->name}.asset.php";
            wp_register_script(
                "wc-{$this->name}-payments-blocks",
                plugin_dir_url(__DIR__) . "assets/js/build/{$this->name}.js",
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );

            wp_enqueue_script("wc-{$this->name}-payments-blocks");
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
