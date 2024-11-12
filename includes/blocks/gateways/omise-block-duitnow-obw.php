<?php

class Omise_Block_DuitNow_OBW extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_duitnow_obw';

    public function set_additional_data() {
        $this->additional_data = [ 'banks' => $this->gateway->get_bank_list() ];
    }
}
