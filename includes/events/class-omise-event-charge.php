<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 6.x.x
 */
abstract class Omise_Event_Charge extends Omise_Event {
	/**
	 * Charge object returned from Omise API.
	 * This object will be available after the event is validated.
	 *
	 * @var OmiseCharge
	 */
	protected OmiseCharge $charge;

	/**
	 * @inheritdoc
	 */
	public function validate() {
		if ( 'charge' !== $this->data['object'] || ! isset( $this->data['metadata']['order_id'] ) ) {
			return false;
		}

		$this->order = wc_get_order( $this->data['metadata']['order_id'] );

		// Making sure that an event's charge id is identical with an order transaction id.
		if ( ! $this->order || $this->order->get_transaction_id() !== $this->data['id'] ) {
			return false;
		}

		$this->charge = $this->fetch_charge();

		return true;
	}

	protected function fetch_charge() {
		return OmiseCharge::retrieve( $this->data['id'] );
	}
}
