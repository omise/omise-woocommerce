<?php

class Omise_Block_Installment extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_installment';

    public function set_additional_data() {
        $viewData = $this->gateway->get_view_data();

        $this->additional_data = [
            'installments_enabled' => $viewData['installments_enabled'],
            'total_amount' => $viewData['total_amount'],
            'currency' => $viewData['currency'],
            'public_key'  => Omise_Setting::instance()->public_key(),
            'installment_min_limit' => $viewData['installment_min_limit']
        ];
    }

    public function get_payment_method_script_handles() {
        $script_asset_path =  __DIR__ . "/../assets/js/build/{$this->name}.asset.php";
        $script_asset = file_exists( $script_asset_path )
            ? require $script_asset_path
            : [
                'dependencies' => [],
                'version' => '1.0.0'
            ];

        if (!wp_script_is("wc-{$this->name}-payments-blocks", 'enqueued')) {
            // Load the script related to OmiseJS in checkout
            if (is_checkout()) {
                wp_enqueue_script(
                    'omise-installment-js',
                    plugins_url( '../../../assets/javascripts/omise-installment-form.js', __FILE__ ),
                    ['omise-js'],
                    OMISE_WOOCOMMERCE_PLUGIN_VERSION,
                    true
                );

                $script_asset['dependencies'] = array_merge($script_asset['dependencies'], ['omise-installment-js']);
            }

            wp_enqueue_script(
                "wc-{$this->name}-payments-blocks",
                plugin_dir_url(__DIR__) . "assets/js/build/{$this->name}.js",
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );
        }

        return [ "wc-{$this->name}-payments-blocks" ];
    }
}
