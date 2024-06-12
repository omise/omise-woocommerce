<?php

use Brain\Monkey;

trait MockPaymentGateways
{
    protected $wc;

    public function mockWcGateways()
    {
        $wc = (object) [
            'version' => '1.1.0',
            'payment_gateways' => new class {
                function payment_gateways() {
                    // dummy gateway
                    $gateway = new class {
                        public $supports = ['products'];

                        public $backend;

                        public function is_available() {
                            return true;
                        }

                        public function supports() {
                            return $this->supports;
                        }

                        public function get_option() {
                            return false;
                        }

                        public function is_test() {
                            return true;
                        }

                        public function get_existing_cards() {
                            return ['user_logged_in' => false];
                        }

                        public function get_secure_form_config() {
                            return ['secure_form_enabled' => false];
                        }

                        public function get_view_data() {
                            return [
                                'installment_backends' => [],
                                'is_zero_interest' => true,
                                'installment_min_limit' =>  2000
                            ];
                        }

                        public function is_active() {
                            return true;
                        }
                    };

                    $gateway->backend = new class {
                        public $banks = ['bank1', 'bank2'];

                        public function get_available_providers($currency) {
                            return $currency;
                        }

                        public function get_available_banks() {
                            return ['bank1', 'bank2'];
                        }
                    };

                    return [
                        'omise' => $gateway,
                        'omise_promptpay' => $gateway,
                        'omise_atome' => $gateway,
                        'omise_mobilebanking' => $gateway,
                        'omise_installment' => $gateway,
                        'omise_fpx' => $gateway,
                        'omise_duitnow_obw' => $gateway,
                        'omise_konbini' => $gateway,
                    ];
                }
            }
        ];

        Monkey\Functions\expect('WC')->andReturn($wc);
    }
}