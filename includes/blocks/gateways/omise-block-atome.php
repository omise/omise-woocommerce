<?php

class Omise_Block_Atome extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_atome';

    public function set_additional_data() {
        $this->additional_data = $this->gateway->validate_atome_request();
    }
}
