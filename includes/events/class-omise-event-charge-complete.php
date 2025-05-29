<?php

defined( 'ABSPATH' ) || exit;

class Omise_Event_Charge_Complete extends Omise_Event_Charge {
	/**
	 * @var string  of an event name.
	 */
	const EVENT_NAME = 'charge.complete';

	public function is_resolvable() {
	{
		if ( 'yes' === $this->order->get_meta( 'is_omise_payment_resolved' ) || $this->is_attempt_limit_exceeded() ) {
			return true;
		}

		$schedule_action = 'omise_async_webhook_event_handler';
		$schedule_group  = 'omise_async_webhook';
		$data            = array(
			'key'  => self::EVENT_NAME,
			'data' => serialize( $this->data ),
		);
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
	 * @return void
	 */
	public function resolve() {
		if ( ! $this->is_resolvable() ) {
			return;
		}

		$this->order->add_order_note(
			$this->allow_br( 'Omise: Received charge.complete webhook event.' )
		);

		switch ( $this->charge['status'] ) {
			case 'failed':
				$this->handle_failed_charge();
				break;
			case 'successful':
				$this->handle_successful_charge();
				break;
			case 'pending':
				$this->handle_pending_charge();
				break;
			default:
				break;
		}
	}

	/**
	 * Handler failed charge
	 */
	private function handle_failed_charge() {
		if ( $this->order->has_status( 'failed' ) ) {
			return;
		}

		$failure_message = sprintf(
			'%s  (code: %s)',
			Omise()->translate( $this->charge['failure_message'] ),
			$this->charge['failure_code']
		);

		$this->order->add_order_note(
			sprintf(
				$this->allow_br( 'Omise: Payment failed.<br/>%s' ),
				$failure_message
			)
		);

		$this->order->update_status( Omise_Payment::STATUS_FAILED );
	}

	/**
	 * Handler successful charge
	 */
	private function handle_successful_charge() {
		if ( $this->order->has_status( 'processing' ) ) {
			return;
		}
		$this->order->add_order_note(
			sprintf(
				$this->allow_br( 'Omise: Payment successful.<br/>An amount %1$s %2$s has been paid' ),
				$this->order->get_total(),
				$this->order->get_currency()
			)
		);
		$this->order->payment_complete();
	}

	/**
	 * Handler pending charge
	 */
	private function handle_pending_charge() {
		if ( $this->order->has_status( 'processing' ) ) {
			return;
		}
		// Credit Card 3-D Secure with 'authorize only' payment action case.
		if ( $this->charge['authorized'] ) {
			$this->order->update_meta_data( 'is_awaiting_capture', 'yes' );
			$this->order->update_status( 'processing' );
			$this->order->save();
		}
	}

	/**
	 * Allow br from the message.
	 */
	private function allow_br( $message ) {
		return wp_kses( __( $message, 'omise' ), [ 'br' => [] ] ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	}
}
