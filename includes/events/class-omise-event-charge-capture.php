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

	/**
	 * This `charge.capture` event is only being used
	 * to catch a manual-capture action that happens on 'Omise Dashboard'.
	 * For on-store capture, it will be handled by Omise_Payment_Creditcard::process_capture.
	 */
	public function resolve() {
		$this->order->add_order_note( __( 'Omise: Received charge.capture webhook event.', 'omise' ) );

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
				$message = __( 'Omise: Payment successful.<br/>An amount %1$s %2$s has been paid', 'omise' );

				$this->order->add_order_note(
					sprintf(
						wp_kses( $message, array( 'br' => array() ) ),
						$this->order->get_total(),
						$this->order->get_currency()
					)
				);

				if ( ! $this->order->has_status( 'processing' ) ) {
					$this->order->update_status( 'processing' );
				}
			break;
		}

		return;
	}
}
