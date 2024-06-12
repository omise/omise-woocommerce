<?php

class Omise_Block_Konbini extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_konbini';

    public function set_additional_data() {
        $this->additional_data = [];
    }
}
