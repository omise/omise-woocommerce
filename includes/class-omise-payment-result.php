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

	/**
	 * @param \WC_Abstract_Order $order
	 * @param \OmiseCharge       $charge
	 * @param string             $context
	 */
	public function __construct( $order, $charge, $context ) {
		$this->order   = $order;
		$this->charge  = $charge;
		$this->context = $context;
	}

	/**
	 * @param string       $order_id
	 * @param string       $charge
	 * @param string|null  $context
	 *
	 * @static
	 */
	public static function callback( $order_id, $charge, $context = null ) {
		$result = new self( wc_get_order( $order_id ), unserialize( $charge ), $context );
		$result->resolve();
	}

	/**
	 * Resolving order status based on
	 * the result of a particular payment.
	 */
	public function resolve() {
		switch ( strtolower( $this->charge['status'] ) ) {
			case 'successful':
				$this->payment_successful();
				break;

			case 'failed':
				$this->payment_failed();
				break;

			case 'pending':
				if ( ! $this->charge['capture'] && $this->charge['authorized'] ) {
					$this->payment_authorized();
				} else {
					$this->payment_pending();
				}
				break;

			default:
				$this->order->add_order_note( __( 'Unrecognized Omise Charge status.', 'omise' ) );
				break;
		}
	}

	/**
	 * Resolving a case of charge status: successful.
	 */
	public function payment_successful() {
		if ( $this->order->has_status( 'processing' ) ) {
			return;
		}

		$message = wp_kses( __( 'Omise: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' ), array( 'br' => array() ) );
		$this->order->add_order_note( sprintf( $message, $this->order->get_total(), $this->order->get_currency() ) );
		$this->order->payment_complete();
	}

	/**
	 * Resolving a case of charge status: failed.
	 */
	public function payment_failed() {
		if ( $this->order->has_status( 'failed' ) ) {
			return;
		}

		$failure_message = $this->charge['failure_message'] . ' (code: ' . $this->charge['failure_code'] . ')';
		$this->order->add_order_note( sprintf( wp_kses( __( 'Omise: Payment failed.<br/>%s', 'omise' ), array( 'br' => array() ) ), $failure_message ) );
		$this->order->update_status( 'failed' );
	}

	/**
	 * Resolving a case of charge status: pending.
	 */
	public function payment_pending() {
		if ( $this->order->has_status( 'on-hold' ) ) {
			return;
		}

		$message = wp_kses( __(
			'Omise: The payment is being processed.<br/>
			Depending on the payment provider, this may take some time to process.<br/>
			Please do a manual \'Sync Payment Status\' action from the <strong>Order Actions</strong> panel, or check the payment status directly at the Omise Dashboard later.',
			'omise'
		), array( 'br' => array(), 'strong' => array() ) ) ;

		$this->order->add_order_note( $message );
		$this->order->update_status( 'on-hold' );
	}

	/**
	 * Resolving a case of an authorized charge.
	 */
	public function payment_authorized() {
		if ( $this->order->has_status( 'processing' ) ) {
			return;
		}

		$message = wp_kses( __(
			'Omise: The payment is being processed.<br/>
			 An amount %1$s %2$s has been authorized.',
			'omise'
		), array( 'br' => array() ) );

		$this->order->add_order_note( sprintf( $message, $this->order->get_total(), $this->order->get_currency() ) );
		$this->order->update_status( 'processing' );
	}
}
