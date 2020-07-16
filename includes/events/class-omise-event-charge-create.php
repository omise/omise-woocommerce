<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Event_Charge_Create' ) ) {
	return;
}

class Omise_Event_Charge_Create {
	/**
	 * @var string  of an event name.
	 */
	public $event = 'charge.create';

	/**
	 * There are several cases when make a new charge with the following
	 * payment methods that would trigger the 'charge.create' event.
	 *
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Alipay
	 * charge data in payload:
	 *     [status: 'pending' (always)], [authorized: 'false' (always)]
	 *
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Internet Banking
	 * charge data in payload:
	 *     [status: 'pending' (always)], [authorized: 'false' (always)]
	 *
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Credit Card (none 3-D Secure)
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
	 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	 * Credit Card (3-D Secure)
	 * CAPTURE = FALSE
	 * charge data in payload could be one of these sets:
	 *     [status: 'pending'], [authorized: 'false'], [paid: 'false']
	 *     [status: 'failed'], [authorized: 'false'], [paid: 'false']
	 *
	 * CAPTURE = TRUE
	 * charge data in payload could be one of these sets:
	 *     [status: 'pending'], [authorized: 'false'], [paid: 'false']
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

		$order->add_order_note(
			__(
				'Omise: an event charge.create has been caught (webhook).',
				'omise'
			)
		);

		/** 
		 * Note. There is no special case for 'charge.create' event to handle with.
		 *       Basically, just to pass this event in case some 3rd-party developer
		 *       need to add extra process, then they can hook 'omise_handled_event_charge_create' action.
		 */

		/**
		 * Hook after Omise handle an event from webhook.
		 *
		 * @param WC_Order $order  an order object.
		 * @param mixed $data      a data of an event object
		 */
		do_action( 'omise_handled_event_charge_create', $order, $data );

		return;
	}
}
