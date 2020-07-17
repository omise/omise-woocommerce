<?php

defined( 'ABSPATH' ) || exit;

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
 */
class Omise_Event_Charge_Create extends Omise_Event {
	/**
	 * @var string  of an event name.
	 */
	const EVENT_NAME = 'charge.create';

	/**
	 * @inheritdoc
	 */
	public function validate() {
		if ( 'charge' !== $this->data['object'] || ! isset( $this->data['metadata']['order_id'] ) ) {
			return false;
		}

		if ( ! $this->order = wc_get_order( $this->data['metadata']['order_id'] ) ) {
			return false;
		}

		// Making sure that an event's charge id is identical with an order transaction id.
		if ( $this->order->get_transaction_id() !== $this->data['id'] ) {
			return false;
		}

		return true;
	}
}
