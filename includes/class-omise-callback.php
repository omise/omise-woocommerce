<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
 */
class Omise_Callback {
	/**
	 * @var string
	 */
	protected $order_id;

	/**
	 * @var \WC_Abstract_Order
	 */
	protected $order;

	/**
	 * @var \OmiseCharge
	 */
	protected $charge;

	/**
	 * @param \WC_Abstract_Order $order
	 */
	public function __construct( $order_id ) {
		$this->order_id = $order_id;
		$this->order    = wc_get_order( $order_id );
		if ( ! $this->order || ! $this->order instanceof WC_Abstract_Order ) $this->invalid_result();
	}

	public static function execute() {
		$order_id = isset( $_GET['order_id'] ) ? sanitize_text_field( $_GET['order_id'] ) : null;

		$callback = new self( $order_id );
		$callback->validate();
	}

	public function validate() {
		$this->order->add_order_note( __( 'Omise: User has been redirected back from the payment page.', 'omise' ) );

		try {
			$this->charge = OmiseCharge::retrieve( $this->order->get_transaction_id() );

			switch ( strtolower( $this->charge['status'] ) ) {
				case 'successful':
				case 'failed':
				case 'pending':
					WC()->queue()->add(
						'omise_async_payment_result',
						array( 'order_id' => $this->order_id, 'charge' => serialize( $this->charge ), 'context' => 'callback' ),
						'omise-payment-result'
					);

					$resolving_method = strtolower( 'payment_' . $this->charge['status'] );
					$this->$resolving_method();
					break;

				default:
					throw new Exception( __( 'Unrecognized Omise Charge status.', 'omise' ) );
					break;
			}
		} catch ( Exception $e ) {
			$this->order->add_order_note(
				sprintf(
					wp_kses( __( 'OMISE: Unable to validate the result.<br/>%s', 'omise' ), array( 'br' => array() ) ),
					$e->getMessage()
				)
			);

			$this->invalid_result();
		}
	}

	/**
	 * Resolving a case of undefined charge status
	 */
	protected function invalid_result() {
		$message = __(
			'<strong>We cannot validate your payment result:</strong><br/>
			 Note that your payment may have already been processed.<br/>
			 Please contact our support team if you have any questions.',
			'omise'
		);

		wc_add_notice( wp_kses( $message, array( 'br' => array(), 'strong' => array() ) ), 'error' );
		wp_redirect( wc_get_checkout_url() );
		exit;
	}

	/**
	 * Resolving a case of charge status: successful.
	 */
	protected function payment_successful() {
		WC()->cart->empty_cart();
		wp_redirect( $this->order->get_checkout_order_received_url() );
		exit;
	}

	/**
	 * Resolving a case of charge status: pending.
	 */
	protected function payment_pending() {
		// Card authorized case.
		if ( ! $this->charge['capture'] && $this->charge['authorized'] ) {
			// Remove cart
			WC()->cart->empty_cart();
		}

		wp_redirect( $this->order->get_checkout_order_received_url() );
		exit;
	}

	/**
	 * Resolving a case of charge status: failed.
	 */
	protected function payment_failed() {
		$message         = __( 'It seems we\'ve been unable to process your payment properly:<br/>%s', 'omise' );
		$failure_message = $this->charge['failure_message'] . ' (code: ' . $this->charge['failure_code'] . ')';

		wc_add_notice( sprintf( wp_kses( $message, array( 'br' => array() ) ), $failure_message ), 'error' );
		wp_redirect( wc_get_checkout_url() );
		exit;
	}
}
