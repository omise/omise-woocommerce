<?php
if (! class_exists('OmisePluginHelperMailer')) {
    class OmisePluginHelperMailer
    {   
        /**
         * Due to payment method like paynow the new order email was disable then customize plugin to send when processing instead
         * but need to resend for other payment method otherwise merchant will get duplicate email once new order created
         * @param string $order_id
         * @param string|WC_Order $order
         */     
        public static function processing_admin_notification($charge)
        {
            if (! isset($charge['object']) || $charge['object'] !== 'charge')
                return false;

            return true;
        }
        
        /**
         * Due to payment method like paynow the email send to merchant with status on-hold will confuse the merchant so let's disable
         * @param string $recipient
         * @param string|WC_Order $order
         * @return mixed|string
         */
        public function disable_merchant_order_on_hold( $recipient, $order ) {
            if (is_a($order, 'WC_Order') && $order->get_status() == 'on-hold' ) $recipient = '';
            return $recipient;
        }
    }
}