<?php

class Omise_Block_GooglePay extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_googlepay';

    public function set_additional_data() {
        $this->additional_data = $this->gateway->googlepay_config;
    }
}
