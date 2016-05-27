<?php
if (! class_exists('OmisePluginHelperCharge')) {
    class OmisePluginHelperCharge
    {
        /**
         * @param string  $currency
         * @param integer $amount
         * @return string
         */
        public static function amount($currency, $amount)
        {
            switch (strtoupper($currency)) {
                case 'THB':
                    // Convert to satang unit
                    $amount = $amount * 100;
                    break;

                case 'JPY':
                    break;

                default:
                    break;
            }

            return $amount;
        }
    }
}