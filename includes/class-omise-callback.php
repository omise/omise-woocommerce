<?php
defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
 */
class Omise_Callback {
	public function execute() {
		$order   = $this->retrieve_order();
		$payment = $this->retrieve_payment( $order );

		$order->add_order_note( __( 'OMISE: Validating the payment result...', 'omise' ) );

		try {
			$charge = OmiseCharge::retrieve( $order->get_transaction_id() );

			$resolving_method = strtolower( 'payment_' . $charge['status'] );
			if ( ! method_exists( $payment, $resolving_method ) ) {
				throw new Exception( __( 'Unrecognized Omise Charge status.', 'omise' ) );
			}

			$payment->result_handler()->$resolving_method( $order, $charge );
		} catch ( Exception $e ) {
			$order->add_order_note(
				sprintf(
					wp_kses( __( 'OMISE: Unable to validate the result.<br/>%s', 'omise' ), array( 'br' => array() ) ),
					$e->getMessage()
				)
			);

			$this->invalid_result();
		}
	}

	/**
	 * @return WC_Abstract_Order | HTML redirect  Returning an object that is extended from WC_Order class.
	 */
	protected function retrieve_order() {
		$order_id = isset( $_GET['order_id'] ) ? sanitize_text_field( $_GET['order_id'] ) : null;
		return wc_get_order( $order_id ) ?: $this->invalid_result();
	}

	/**
	 * @param WC_Abstract_Order $order
	 *
	 * @return Omise_Payment | HTML redirect  Returning an object that is extended from Omise_Payment class.
	 */
	protected function retrieve_payment( $order ) {
		$payment = Omise_Payment_Factory::get_payment_method( $order->get_payment_method() );
		return $payment ?: $this->invalid_result();
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
}
