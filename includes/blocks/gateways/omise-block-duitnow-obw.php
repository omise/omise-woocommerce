<?php

class Omise_Block_DuitNow_OBW extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_duitnow_obw';

    public function set_additional_data() {
        if (!$this->gateway->backend) {
            $this->gateway->init_payment_config();
        }

        $banks = $this->gateway->backend ? $this->gateway->backend->banks : [];
        $this->additional_data = [ 'banks' => $banks ];
    }
}
