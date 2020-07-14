<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
 */
class Omise_Payment_Result {
	/**
	 * @var \WC_Abstract_Order
	 */
	protected $order;

	/**
	 * @var \OmiseCharge
	 */
	protected $charge;

	/**
	 * @var string
	 */
	protected $context;

	public function __construct( $order, $charge, $context ) {
		$this->order   = $order;
		$this->charge  = $charge;
		$this->context = $context;
	}

	public static function callback( $order_id, $charge, $context ) {
		$result = new self( wc_get_order( $order_id ), unserialize( $charge ), $context );
		$result->resolve();
	}

	public function resolve() {
		switch ( strtolower( $this->charge['status'] ) ) {
			case 'successful':
				$this->payment_successful();
				break;

			case 'failed':
				$this->payment_failed();
				break;

			case 'pending':
				$this->payment_pending();
				break;

			default:
				throw new Exception( __( 'Unrecognized Omise Charge status.', 'omise' ) );
				break;
		}
	}

	/**
	 * Resolving a case of charge status: successful.
	 */
	public function payment_successful() {
		$message = __( 'OMISE: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' );

		$this->order->payment_complete();
		$this->order->add_order_note(
			sprintf(
				wp_kses( $message, array( 'br' => array() ) ),
				$this->order->get_total(),
				$this->order->get_order_currency()
			)
		);
	}

	/**
	 * Resolving a case of charge status: successful.
	 */
	public function payment_failed() {
		$failure_message = $this->charge['failure_message'] . ' (code: ' . $this->charge['failure_code'] . ')';

		$this->order->add_order_note( sprintf( wp_kses( __( 'OMISE: Payment failed.<br/>%s', 'omise' ), array( 'br' => array() ) ), $failure_message ) );
		$this->order->update_status( 'failed' );
	}

	/**
	 * Resolving a case of charge status: successful.
	 */
	public function payment_pending() {
		if ( ! $this->charge['capture'] && $this->charge['authorized'] ) {
			// Card authorized case.
			$message = __(
				'Omise: The payment is being processed.<br/>
				 An amount %1$s %2$s has been authorized.',
				'omise'
			);

			$this->order->add_order_note(
				sprintf(
					wp_kses( $message, array( 'br' => array() ) ),
					$this->order->get_total(),
					$this->order->get_order_currency()
				)
			);
		} else {
			// Offsite case.
			$message = __(
				'Omise: The payment is being processed.<br/>
				Depending on the payment provider, this may take some time to process.<br/>
				Please do a manual \'Sync Payment Status\' action from the <strong>Order Actions</strong> panel, or check the payment status directly at the Omise Dashboard later.',
				'omise'
			);

			$this->order->add_order_note( wp_kses( $message, array( 'br' => array(), 'strong' => array() ) ) );
			$this->order->update_status( 'on-hold' );
		}
	}
}
