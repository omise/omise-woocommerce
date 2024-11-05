<?php

class Omise_Block_InternetBanking extends Omise_Block_Payment {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'omise_internetbanking';

    public function set_additional_data() {
        $this->additional_data = [];
    }
}
