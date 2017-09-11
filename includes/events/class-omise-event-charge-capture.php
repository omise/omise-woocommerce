<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Event_Charge_Capture' ) ) {
	return;
}

class Omise_Event_Charge_Capture {
	/**
	 * @var string  of an event name.
	 */
	public $event = 'charge.capture';

	/**
	 * There are several cases that can trigger the 'charge.capture' event.
	 *
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Credit Card
	 * charge data in payload will be:
	 *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
	 *
	 * @param  mixed $data
	 *
	 * @return void
	 */
	public function handle( $data ) {
		if ( 'charge' !== $data->object || ! isset( $data->metadata->order_id ) ) {
			return;
		}

		if ( ! $order = wc_get_order( $data->metadata->order_id ) ) {
			return;
		}

		$order->add_order_note(
			__(
				'Omise: an event charge.capture has been caught (webhook).',
				'omise'
			)
		);

		switch ($data->status) {
			case 'successful':
				// Backward compatible with Omise API version 2014-07-27 by checking if 'captured' exist.
				$paid = isset( $data->captured ) ? $data->captured : $data->paid;

				if ( $data->authorized && $paid ) {
					$order->add_order_note(
						sprintf(
							wp_kses(
								__( 'Omise: Payment successful (manual capture).<br/>An amount %1$s %2$s has been paid', 'omise' ),
								array( 'br' => array() )
							),
							$order->get_total(),
							$order->get_order_currency()
						)
					);

					$order->payment_complete( $data->id );
				}

				break;
			
			default:
				$order->add_order_note(
					wp_kses(
						__( 'Omise: Payment invalid.<br/>There was something wrong in the Webhook payload. Please contact Omise support team to investigate further.', 'omise' ),
						array( 'br' => array() )
					)
				);

				break;
		}

		/**
		 * Hook after Omise handle an event from webhook.
		 *
		 * @param WC_Order $order  an order object.
		 * @param mixed $data      a data of an event object
		 */
		do_action( 'omise_handled_event_charge_capture', $order, $data );

		return;
	}
}
