<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Event_Charge_Complete' ) ) {
	return;
}

class Omise_Event_Charge_Complete {
	/**
	 * @var string  of an event name.
	 */
	public $event = 'charge.complete';

	/**
	 * There are several cases with the following payment methods
	 * that would trigger the 'charge.complete' event.
	 *
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Alipay
	 * charge data in payload:
	 *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
	 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
	 *
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Internet Banking
	 * charge data in payload:
	 *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
	 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
	 *
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Credit Card (3-D Secure)
	 * CAPTURE = FALSE
	 * charge data in payload could be one of these sets:
	 *     [status: 'pending'], [authorized: 'true'], [paid: 'false']
	 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
	 *
	 * CAPTURE = TRUE
	 * charge data in payload could be one of these sets:
	 *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
	 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
	 *
	 * @param  mixed $data
	 *
	 * @return void
	 */
	public function handle( $data ) {
		if ( 'charge' !== $data['object'] || ! isset( $data['metadata']['order_id'] ) ) {
			return;
		}

		if ( ! $order = wc_get_order( $data['metadata']['order_id'] ) ) {
			return;
		}

		// Making sure that an event's charge id is identical with an order transaction id.
		if ( $order->get_transaction_id() !== $data['id'] ) {
			return;
		}

		$order->add_order_note( __( 'Omise: Receiving charge.complete webhook event.', 'omise' ) );

		WC()->queue()->add(
			'omise_async_payment_result',
			array( 'order_id' => $data['metadata']['order_id'], 'charge' => serialize( $data ), 'context' => 'webhook' ),
			'omise-payment-result'
		);

		/**
		 * Hook after Omise handle an event from webhook.
		 *
		 * @param WC_Order $order  an order object.
		 * @param mixed $data      a data of an event object
		 */
		do_action( 'omise_handled_event_charge_complete', $order, $data );

		return;
	}
}
