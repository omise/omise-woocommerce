<?php

defined( 'ABSPATH' ) || exit;

/**
 * There are several cases that can trigger the 'charge.capture' event.
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * Credit Card
 * charge data in payload will be:
 *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
 *
 */
class Omise_Event_Charge_Capture extends Omise_Event {
	/**
	 * @var string  of an event name.
	 */
	const EVENT_NAME = 'charge.capture';

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
