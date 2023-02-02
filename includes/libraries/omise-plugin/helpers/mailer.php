<?php
if (! class_exists('OmisePluginHelperMailer')) {
    class OmisePluginHelperMailer {
        /**
         * Due to payment method like paynow the new order email was disable then customize plugin to send when processing instead
         * but need to resend for other payment method otherwise merchant will get duplicate email once new order created
         * @param string $order_id
         * @param string|WC_Order $order
         */
        public static function processing_admin_notification( $order_id, $order ) {
            $payment_gateway = wc_get_payment_gateway_by_order( $order );
            if (is_a( $payment_gateway, 'Omise_Payment' ) && $payment_gateway->is_enabled_processing_notification()) {
                WC()->mailer()->get_emails()['WC_Email_New_Order']->trigger( $order_id );
            }
        }

        /**
         * Due to payment method like paynow the email send to merchant with status on-hold will confuse the merchant so let's disable
         * @param string $recipient
         * @param string|WC_Order $order
         * @return mixed|string
         */
        public static function disable_merchant_order_on_hold( $recipient, $order ) {
            $payment_gateway = wc_get_payment_gateway_by_order( $order );
            if (is_a($order, 'WC_Order') && 
                is_a( $payment_gateway, 'Omise_Payment' ) &&
                $order->get_status() == 'on-hold' 
            ) {
                $recipient = '';
            }
            return $recipient;
        }
    }
}
