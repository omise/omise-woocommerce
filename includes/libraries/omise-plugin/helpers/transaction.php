<?php
defined('OMISE_DASHBOARD_URL') || define('OMISE_DASHBOARD_URL', 'https://dashboard.omise.co');

if (! class_exists('OmisePluginHelperTransaction')) {
    class OmisePluginHelperTransaction
    {
        /**
         * @param string $transaction_id
         * @return string
         */
        public static function type($transaction_id)
        {
            preg_match('/^[a-zA-Z]+_/', $transaction_id, $matches);

            $transaction_type = substr($matches[0], 0, -1);
            switch ($transaction_type) {
                case 'chrg':
                    $type = 'Charge';
                    break;

                case 'trsf':
                    $type = 'Transfer';
                    break;

                case 'rfnd':
                    $type = 'Refund';
                    break;

                case 'dspt':
                    $type = 'Dispute';
                    break;

                default:
                    $type = 'Unknown';
                    break;
            }

            return $type;
        }

        /**
         *
         * @param OmiseTransaction object $transaction
         * @param string                  $account_type
         * @return string
         */
        public static function url($transaction, $account_type = '')
        {
            if (! isset($transaction['object']) || $transaction['object'] !== 'transaction')
                return OMISE_DASHBOARD_URL;

            if ($account_type !== '') {
                $url = OMISE_DASHBOARD_URL . "/" . $account_type;
            } else {
                // Pattern that would looks for `string_test_`
                // i.e. chrg_test_51gussmcn7cu7j8feqw
                preg_match('/^[a-zA-Z]+_test_/', $transaction['source'], $is_test_account);
                $url = OMISE_DASHBOARD_URL . "/" . ($is_test_account ? 'test' : 'live');
            }

            // Pattern that would looks for `string_`
            // i.e. chrg_test_51gussmcn7cu7j8feqw
            preg_match('/^[a-zA-Z]+_/', $transaction['source'], $matches);
            $transaction_type = substr($matches[0], 0, -1);

            switch ($transaction_type) {
                case 'chrg':
                    $url .= "/charges/{$transaction['source']}";
                    break;

                case 'trsf':
                    $url .= "/transfers/{$transaction['source']}";
                    break;

                case 'rfnd':
                    $url .= "/refunds/{$transaction['source']}";
                    break;

                case 'dspt':
                    $url .= "/disputes/{$transaction['source']}";
                    break;

                default:
                    break;
            }

            return $url;
        }
    }
}
