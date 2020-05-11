<?php

defined( 'ABSPATH' ) || exit;

class Omise_Event_Charge_Complete extends Omise_Event {
	/**
	 * @var string  of an event name.
	 */
	const EVENT_NAME = 'charge.complete';

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

	public function is_resolvable() {
		if ( 'yes' === $this->order->get_meta( 'is_omise_payment_resolved' ) || $this->is_attempt_limit_exceeded() ) {
			return true;
		}

		$schedule_action = 'omise_async_webhook_event_handler';
		$schedule_group  = 'omise_async_webhook';
		$data            = array( 'key' => self::EVENT_NAME, 'data' => serialize( $this->data ) );
		$this->schedule_single( $schedule_action, $data, $schedule_group );
		return false;
	}

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
	public function resolve() {
		if ( ! $this->is_resolvable() ) return;

		$this->order->add_order_note( __( 'Omise: Received charge.complete webhook event.', 'omise' ) );

		switch ( $this->data['status'] ) {
			case 'failed':
				if ( $this->order->has_status( 'failed' ) ) {
					return;
				}

				$message         = __( 'Omise: Payment failed.<br/>%s', 'omise' );
				$failure_message = Omise()->translate( $this->data['failure_message'] ) . ' (code: ' . $this->data['failure_code'] . ')';
				$this->order->add_order_note(
					sprintf(
						wp_kses( $message, array( 'br' => array() ) ),
						$failure_message
					)
				);
				$this->order->update_status( 'failed' );
				break;

			case 'successful':
				if ( $this->order->has_status( 'processing' ) ) {
					return;
				}

				$message = __( 'Omise: Payment successful.<br/>An amount %1$s %2$s has been paid', 'omise' );

				$this->order->add_order_note(
					sprintf(
						wp_kses( $message, array( 'br' => array() ) ),
						$this->order->get_total(),
						$this->order->get_currency()
					)
				);
				$this->order->payment_complete();
				break;
			
			case 'pending':
				if ( $this->order->has_status( 'processing' ) ) {
					return;
				}

				// Credit Card 3-D Secure with 'authorize only' payment action case.
				if ( $this->data['authorized'] ) {
					$this->order->update_status( 'processing' );
				}
				break;
		}

		return;
	}
}
