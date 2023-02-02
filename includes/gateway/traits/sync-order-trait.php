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
	public function sync_payment( $order ) {
		$this->load_order( $order );

		try {
			$charge = OmiseCharge::retrieve( $this->get_charge_id_from_order() );

			/**
			 * Backward compatible with WooCommerce v2.x series
			 * This case is likely not going to happen anymore as this was provided back then
			 * when Omise-WooCommerce was introducing of adding charge.id into WC Order transaction id.
			 **/
			if ( ! $this->order()->get_transaction_id() ) {
				$this->set_order_transaction_id( $charge['id'] );
			}

			switch ( $charge['status'] ) {
				case Omise_Payment::STATUS_SUCCESSFUL:
					$this->sync_order_handle_charge_successful($charge);
					break;
				case Omise_Payment::STATUS_FAILED:
					$this->sync_order_handle_charge_failed($charge);
					break;
				case Omise_Payment::STATUS_PENDING:
					$this->sync_order_handle_charge_pending();
					break;
				case Omise_Payment::STATUS_EXPIRED:
					$this->sync_order_handle_charge_expired();
					break;
				case Omise_Payment::STATUS_REVERSED:
					$this->sync_order_handle_charge_reversed();
					break;
				default:
					throw new Exception(
						__( 'Cannot read the payment status. Please try sync again or contact Opn Payments support team at support@omise.co if you have any questions.', 'omise' )
					);
					break;
			}
		} catch ( Exception $e ) {
			$message = $this->allow_br('Opn Payments: Sync failed (manual sync).<br/>%s.');
			$order->add_order_note( sprintf( $message, $e->getMessage() ) );
		}
	}

    /**
     * This function handle successful charge, when sync order action was called
     */
    function sync_order_handle_charge_successful($charge)
    {
        // Omise API 2017-11-02 uses `refunded`, 
        // Omise API 2019-05-29 uses `refunded_amount`.
        $refunded_amount = isset($charge['refunded_amount'])
            ? $charge['refunded_amount']
            : $charge['refunded'];

        $fullyRefunded = $refunded_amount == $charge['funding_amount'];
        $partiallyRefunded = $refunded_amount < $charge['funding_amount'];
        $total_amount = $this->order()->get_total();
        $currency = $this->order()->get_currency();

        $this->delete_capture_metadata();

        if ($fullyRefunded) {
            if (!$this->order()->has_status(Omise_Payment::STATUS_REFUNDED)) {
                $this->order()->update_status(Omise_Payment::STATUS_REFUNDED);
            }
            $this->order()->add_order_note(
                sprintf(
                    $this->allow_br('Opn Payments: Payment refunded.<br/>An amount %1$s %2$s has been refunded (manual sync).'),
                    $total_amount,
                    $currency
                )
            );
            return;
        }

        if($partiallyRefunded) {
            $this->order()->add_order_note(
                sprintf(
                    $this->allow_br('Opn Payments: Payment partially refunded.<br/>An amount %1$s %2$s has been refunded (manual sync).'),
                    Omise_Money::to_readable($refunded_amount, $currency),
                    $currency
                )
            );
            return;
        }

        $this->order()->add_order_note(
            sprintf(
                $this->allow_br('Opn Payments: Payment successful.<br/>An amount %1$s %2$s has been paid (manual sync).'),
                $total_amount,
                $$currency
            )
        );
        if (!$this->order()->is_paid()) {
            $this->order()->payment_complete();
        }
    }

    /**
     * This function handle failed charge, when sync order action was called
     */
    function sync_order_handle_charge_failed($charge)
    {
        $this->delete_capture_metadata();
        $this->order()->add_order_note(
            sprintf(
                $this->allow_br('Opn Payments: Payment failed.<br/>%s (code: %s) (manual sync).'),
                Omise()->translate($charge['failure_message']),
                $charge['failure_code']
            )
        );
        if (!$this->order()->has_status(Omise_Payment::STATUS_FAILED)) {
            $this->order()->update_status(Omise_Payment::STATUS_FAILED);
        }
    }

    /**
     * This function handle pending charge, when sync order action was called
     */
    function sync_order_handle_charge_pending()
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
    function sync_order_handle_charge_expired()
    {
        $this->delete_capture_metadata();
        $this->order()->add_order_note(
            $this->allow_br('Opn Payments: Payment expired. (manual sync).'));
        if ( ! $this->order()->has_status( Omise_Payment::STATUS_CANCELLED ) ) {
            $this->order()->update_status( Omise_Payment::STATUS_CANCELLED );
        }
    }

    /**
     * This function handle reversed charge, when sync order action was called
     */
    function sync_order_handle_charge_reversed()
    {
        $this->delete_capture_metadata();
        $this->order()->add_order_note(
            $this->allow_br('Opn Payments: Payment reversed. (manual sync).'));
        if ( ! $this->order()->has_status( Omise_Payment::STATUS_CANCELLED ) ) {
            $this->order()->update_status( Omise_Payment::STATUS_CANCELLED );
        }
    }

    /**
     * allow br from the message.
     */
    function  allow_br($message)
    {
        return wp_kses(__($message, 'omise'), ['br' => []]);
    }
}
