<?php
if (! class_exists('OmisePluginHelperWcOrder')) {
    class OmisePluginHelperWcOrder
    {
        /**
         * @param int|WC_Order $order_id
         * @return string
         */
        public static function get_order_key_by_id( $order_id ) {
            $order = wc_get_order($order_id);
    
            if ($order && !is_wp_error($order)) {
                $order_key = $order->get_order_key();
            }
    
            return $order_key;
        }
    }
}
