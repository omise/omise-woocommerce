<?php
if (! class_exists('OmisePluginHelperCharge')) {
    class OmisePluginHelperCharge
    {
        /**
         * @param \omise-php\OmiseCharge $charge
         * @return boolean
         */
        public static function isChargeObject($charge)
        {
            if (! isset($charge['object']) || $charge['object'] !== 'charge')
                return false;

            return true;
        }

        /**
         * @param \omise-php\OmiseCharge $charge
         * @return boolean
         */
        public static function isAuthorized($charge)
        {
            if (self::isChargeObject($charge)) {
                if ($charge['authorized'] === true)
                    return true;
            }

            return false;
        }

        /**
         * @param \omise-php\OmiseCharge $charge
         * @return boolean
         */
        public static function isPaid($charge)
        {
            if (self::isChargeObject($charge)) {
                if ($charge['paid'] === true)
                    return true;
            }

            return false;
        }

        /**
         * @param \omise-php\OmiseCharge $charge
         * @return boolean
         */
        public static function isFailed($charge)
        {
            if (! self::isChargeObject($charge))
                return true;

            if ((! is_null($charge['failure_code']) && $charge['failure_code'] !== "")
                || (! is_null($charge['failure_message']) && $charge['failure_message'] !== ""))
                return true;

            if (strtoupper($charge['status']) === 'FAILED')
                return true;

            return false;
        }
    }
}