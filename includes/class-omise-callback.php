<?php

defined( 'ABSPATH' ) || exit;

/**
 * @since 4.0
 */
class Omise_Callback {
	/**
	 * @var \Omise_Callback
	 */
	protected static $the_instance = null;

	/**
	 * @static
	 *
	 * @return \Omise_Callback - The instance.
	 */
	public static function instance() {
		if ( is_null( self::$the_instance ) ) {
			self::$the_instance = new self();
		}

		return self::$the_instance;
	}

	public static function execute() {
		$callback = self::instance();

		$order   = $callback->retrieve_order();
		$payment = $callback->retrieve_payment( $order );

		$order->add_order_note( __( 'OMISE: Validating the payment result...', 'omise' ) );

		try {
			$charge = OmiseCharge::retrieve( $order->get_transaction_id() );

			$resolving_method = strtolower( 'payment_' . $charge['status'] );
			if ( ! method_exists( $callback, $resolving_method ) ) {
				throw new Exception( __( 'Unrecognized Omise Charge status.', 'omise' ) );
			}

			$callback->$resolving_method( $order, $charge );
		} catch ( Exception $e ) {
			$order->add_order_note(
				sprintf(
					wp_kses( __( 'OMISE: Unable to validate the result.<br/>%s', 'omise' ), array( 'br' => array() ) ),
					$e->getMessage()
				)
			);

			$callback->invalid_result();
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

	/**
	 * Resolving a case of charge status: successful.
	 *
	 * @param WC_Abstract_Order $order
	 * @param OmiseCharge       $charge
	 */
	protected function payment_successful( $order, $charge ) {
		$message = __( 'OMISE: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' );

		$order->payment_complete();
		$order->add_order_note(
			sprintf(
				wp_kses( $message, array( 'br' => array() ) ),
				$order->get_total(),
				$order->get_order_currency()
			)
		);

		WC()->cart->empty_cart();
		wp_redirect( $order->get_checkout_order_received_url() );
		exit;
	}

	/**
	 * Resolving a case of charge status: pending.
	 *
	 * @param WC_Abstract_Order $order
	 * @param OmiseCharge       $charge
	 */
	protected function payment_pending( $order, $charge ) {
		if ( ! $charge['capture'] && $charge['authorized'] ) {
			// Card authorized case.
			$message = __(
				'Omise: The payment is being processed.<br/>
				 An amount %1$s %2$s has been authorized.',
				'omise'
			);

			$order->add_order_note(
				sprintf(
					wp_kses( $message, array( 'br' => array() ) ),
					$order->get_total(),
					$order->get_order_currency()
				)
			);

			// Remove cart
			WC()->cart->empty_cart();
			wp_redirect( $order->get_checkout_order_received_url() );
			exit;
		}

		// Offsite case.
		$message = __(
			'Omise: The payment is being processed.<br/>
			 Depending on the payment provider, this may take some time to process.<br/>
			 Please do a manual \'Sync Payment Status\' action from the <strong>Order Actions</strong> panel, or check the payment status directly at the Omise Dashboard later.',
			'omise'
		);
		$order->add_order_note( wp_kses( $message, array( 'br' => array(), 'strong' => array() ) ) );
		$order->update_status( 'on-hold' );
		wp_redirect( $order->get_checkout_order_received_url() );
		exit;
	}

	/**
	 * Resolving a case of charge status: failed.
	 *
	 * @param WC_Abstract_Order $order
	 * @param OmiseCharge       $charge
	 */
	protected function payment_failed( $order, $charge ) {
		$message         = __( 'It seems we\'ve been unable to process your payment properly:<br/>%s', 'omise' );
		$failure_message = $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')';

		$order->add_order_note(
			sprintf( wp_kses( __( 'OMISE: Payment failed.<br/>%s', 'omise' ), array( 'br' => array() ) ), $failure_message )
		);

		// Offsite case.
		if ( ! is_null( $charge['source'] ) && 'redirect' === $charge['source']['flow'] ) {
			$order->update_status( 'failed' );
		}

		wc_add_notice( sprintf( wp_kses( $message, array( 'br' => array() ) ), $failure_message ), 'error' );
		wp_redirect( wc_get_checkout_url() );
		exit;
	}
}
