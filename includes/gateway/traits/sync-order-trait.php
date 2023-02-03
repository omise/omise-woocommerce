<?php

trait Sync_Order
{
    /**
     * Retrieve a charge by a given charge id (that attach to an order).
     * Find some diff, then merge it back to WooCommerce system.
     *
     * @param  WC_Order $order WooCommerce's order object
     *
     * @return void
     *
     * @see    WC_Meta_Box_Order_Actions::save( $post_id, $post )
     * @see    woocommerce/includes/admin/meta-boxes/class-wc-meta-box-order-actions.php
     */
    public function sync_payment($order)
    {
        $this->load_order($order);

        try {
            $charge = OmiseCharge::retrieve($this->get_charge_id_from_order());

            /**
             * Backward compatible with WooCommerce v2.x series
             * This case is likely not going to happen anymore as this was provided back then
             * when Omise-WooCommerce was introducing of adding charge.id into WC Order transaction id.
             **/
            if (!$this->order()->get_transaction_id()) {
                $this->set_order_transaction_id($charge['id']);
            }

            switch ($charge['status']) {
                case Omise_Payment::STATUS_SUCCESSFUL:
                    $this->handle_successful_charge($charge);
                    break;
                case Omise_Payment::STATUS_FAILED:
                    $this->handle_failed_charge($charge);
                    break;
                case Omise_Payment::STATUS_PENDING:
                    $this->handle_pending_charge();
                    break;
                case Omise_Payment::STATUS_EXPIRED:
                    $this->handle_expired_charge();
                    break;
                case Omise_Payment::STATUS_REVERSED:
                    $this->handle_reversed_charge();
                    break;
                default:
                    throw new Exception(
                        __('Cannot read the payment status. Please try sync again or contact Opn Payments support team at support@omise.co if you have any questions.', 'omise')
                    );
                    break;
            }
        } catch (Exception $e) {
            $message = $this->allow_br('Opn Payments: Sync failed (manual sync).<br/>%s.');
            $order->add_order_note(sprintf($message, $e->getMessage()));
        }
    }

    /**
     * This function handle successful charge, 
     * and add order not when order is partially or full refunded 
     * when sync order action was called
     */
    private function handle_successful_charge($charge)
    {
        // Omise API 2017-11-02 uses `refunded`, 
        // Omise API 2019-05-29 uses `refunded_amount`.
        $refunded_amount = isset($charge['refunded_amount'])
            ? $charge['refunded_amount']
            : $charge['refunded'];

        $fullyRefunded = $refunded_amount == $charge['funding_amount'];
        $partiallyRefunded = $refunded_amount > 0 && $refunded_amount < $charge['funding_amount'];
        $total_amount = $this->order->get_total();
        $currency = $this->order->get_currency();

        if ($fullyRefunded) {
            $this->handle_fully_refunded_charge($total_amount, $currency);
            return;
        }
        if ($partiallyRefunded) {
            $this->handle_partially_refunded_charge($refunded_amount, $currency);
            return;
        }

        $this->order->add_order_note(
            sprintf(
                $this->allow_br('Opn Payments: Payment successful.<br/>An amount %1$s %2$s has been paid (manual sync).'),
                $total_amount,
                $currency
            )
        );

        $this->delete_capture_metadata();

        if (!$this->order->is_paid()) {
            $this->order->payment_complete();
        }
    }

    /**
     * This function handle fully refunded charge, when sync order action was called
     */
    private function handle_fully_refunded_charge($amount, $currency)
    {
        $this->update_order_status(Omise_Payment::STATUS_REFUNDED);
        $this->order->add_order_note(
            sprintf(
                $this->allow_br('Opn Payments: Payment refunded.<br/>An amount %1$s %2$s has been refunded (manual sync).'),
                $amount,
                $currency
            )
        );
    }

    /**
     * This function handle partially refunded charge, when sync order action was called
     */
    private function handle_partially_refunded_charge($amount, $currency)
    {
        $this->order->add_order_note(
            sprintf(
                $this->allow_br('Opn Payments: Payment partially refunded.<br/>An amount %1$s %2$s has been refunded (manual sync).'),
                Omise_Money::convert_currency_unit($amount, $currency),
                $currency
            )
        );
    }

    /**
     * This function handle failed charge, when sync order action was called
     */
    private function handle_failed_charge($charge)
    {
        $this->delete_capture_metadata();
        $this->order()->add_order_note(
            sprintf(
                $this->allow_br('Opn Payments: Payment failed.<br/>%s (code: %s) (manual sync).'),
                Omise()->translate($charge['failure_message']),
                $charge['failure_code']
            )
        );
        $this->update_order_status(Omise_Payment::STATUS_FAILED);
    }

    /**
     * This function handle pending charge, when sync order action was called
     */
    private function handle_pending_charge()
    {
        $message = $this->allow_br(
            'Opn Payments: Payment is still in progress.<br/>
            You might wait for a moment before click sync the status again 
            or contact Opn Payments support team at support@omise.co 
            if you have any questions (manual sync).'
        );
        $this->order()->add_order_note($message);
    }

    /**
     * This function handle expired charge, when sync order action was called
     */
    private function handle_expired_charge()
    {
        $this->delete_capture_metadata();
        $this->order()->add_order_note(
            $this->allow_br('Opn Payments: Payment expired. (manual sync).')
        );
        $this->update_order_status(Omise_Payment::STATUS_CANCELLED);
    }

    /**
     * This function handle reversed charge, when sync order action was called
     */
    private function handle_reversed_charge()
    {
        $this->delete_capture_metadata();
        $this->order()->add_order_note(
            $this->allow_br('Opn Payments: Payment reversed. (manual sync).')
        );
        $this->update_order_status(Omise_Payment::STATUS_CANCELLED);
    }

    /**
     * update order status
     */
    private function update_order_status($status)
    {
        if (!$this->order()->has_status($status)) {
            $this->order()->update_status($status);
        }
    }

    /**
     * allow br from the message.
     */
    private function  allow_br($message)
    {
        return wp_kses(__($message, 'omise'), ['br' => []]);
    }
}
