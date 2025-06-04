<?php

defined( 'ABSPATH' ) || exit;

/**
 * There are several cases that can trigger the 'charge.capture' event.
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 * Credit Card
 * charge data in payload will be:
 *     [status: 'successful'], [authorized: 'true'], [paid: 'true']
 */
class Omise_Event_Charge_Capture extends Omise_Event_Charge {
	/**
	 * @var string  of an event name.
	 */
	const EVENT_NAME = 'charge.capture';

	/**
	 * This `charge.capture` event is only being used
	 * to catch a manual-capture action that happens on 'Omise Dashboard'.
	 * For on-store capture, it will be handled by Omise_Payment::process_capture.
	 *
	 * @throws Exception If charge status is not successful or failed.
	 */
	public function resolve() {
		$this->order->add_order_note( __( 'Omise: Received charge.capture webhook event.', 'omise' ) );
		$this->order->delete_meta_data( 'is_awaiting_capture' );
		$this->order->save();

		switch ( $this->charge['status'] ) {
			case 'failed':
				if ( $this->order->has_status( 'failed' ) ) {
					return;
				}

				$message         = __( 'Omise: Payment failed.<br/>%s', 'omise' );
				$failure_message = Omise()->translate( $this->charge['failure_message'] ) . ' (code: ' . $this->charge['failure_code'] . ')';
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

			default:
				throw new Exception( 'invalid charge status' );
		}
	}
}
