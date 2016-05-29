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

        /**
         * @param \omise-php\OmiseCharge $charge
         * @return boolean
         */
        public static function isAuthorized($charge)
        {
            if (! isset($charge['object']) || $charge['object'] !== 'charge')
                return false;

            if ($charge['authorized'] === true)
                return true;

            return false;
        }

        /**
         * @param \omise-php\OmiseCharge $charge
         * @return boolean
         */
        public static function isPaid($charge)
        {
            if (! isset($charge['object']) || $charge['object'] !== 'charge')
                return false;

            // support Omise API version '2014-07-27' by checking if 'captured' exist.
            $paid = isset($charge['captured']) ? $charge['captured'] : $charge['paid'];
            if ($paid === true)
                return true;

            return false;
        }
    }
}