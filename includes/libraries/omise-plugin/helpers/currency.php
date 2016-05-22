<?php
defined( 'ABSPATH' ) or die( "No direct script access allowed." );

if ( ! class_exists( 'OmisePluginHelperCurrency' ) ) {
    class OmisePluginHelperCurrency
    {
        /**
         * @param string $currency_code
         */
        public static function isSupport($currency_code) {
            switch (strtoupper($currency_code)) {
                case 'THB':
                case 'JPY':
                    return true;
                    break;
            }

            return false;
        }

        /**
         * @param string  $currency
         * @param integer $amount
         */
        public static function format($currency, $amount) {
            switch (strtoupper($currency)) {
                case 'THB':
                    $amount = "฿" . number_format(($amount / 100), 2);
                    if (preg_match('/\.00$/', $amount)) {
                        $amount = substr($amount, 0, -3);
                    }

                    break;

                case 'JPY':
                    $amount = "¥" . number_format($amount);
                    break;

                default:
                    break;
            }

            return $amount;
        }
    }
}