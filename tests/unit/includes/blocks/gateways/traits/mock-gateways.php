<?php

use Brain\Monkey;

trait MockPaymentGateways
{
    protected $wc;

    public function mockWcGateways()
    {
        $wc = (object) [
            'payment_gateways' => new class {
                function payment_gateways() {
                    // dummy gateway
                    $gateway = new class {
                        public $supports = ['products'];

                        public function is_available() {
                            return true;
                        }

                        public function supports() {
                            return $this->supports;
                        }

                        public function get_option() {
                            return false;
                        }
                    };

                    return [
                        'omise' => $gateway,
                        'omise_promptpay' => $gateway,
                        'omise_atome' => $gateway,
                    ];
                }
            }
        ];
        Monkey\Functions\expect('WC')->andReturn($wc);
    }
}