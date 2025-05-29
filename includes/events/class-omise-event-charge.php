<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 6.x.x
 */
abstract class Omise_Event_Charge extends Omise_Event {
	/**
	 * @inheritdoc
	 */
	public function validate() {
		if ( 'charge' !== $this->data['object'] || ! isset( $this->data['metadata']['order_id'] ) ) {
			return false;
		}

		$this->order = wc_get_order( $this->data['metadata']['order_id'] );
		if ( ! $this->order ) {
			return false;
		}

		// Making sure that an event's charge id is identical with an order transaction id.
		if ( $this->order->get_transaction_id() !== $this->data['id'] ) {
			return false;
		}

		return true;
	}
}
