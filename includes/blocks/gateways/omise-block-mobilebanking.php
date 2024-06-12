<?php

class Omise_Block_Mobile_Banking extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_mobilebanking';

    public function set_additional_data() {
        $currency   = get_woocommerce_currency();
        $this->additional_data = [
            'backends' => $this->gateway->backend->get_available_providers($currency),
        ];
    }
}
