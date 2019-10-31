<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

require_once dirname( __FILE__ ) . '/class-omise-payment.php';

/**
 * @since 3.10
 */
abstract class Omise_Payment_Offsite extends Omise_Payment {
	/**
	 * @inheritdoc
	 */
	abstract public function charge( $order_id, $order );

	/**
	 * @inheritdoc
	 */
	public function result( $order_id, $order, $charge ) {
		if ( self::STATUS_FAILED === $charge['status'] ) {
			return $this->payment_failed( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
		}

		if ( self::STATUS_PENDING === $charge['status'] ) {
			$order->add_order_note( sprintf( __( 'Omise: Redirecting buyer to %s', 'omise' ), esc_url( $charge['authorize_uri'] ) ) );

			return array (
				'result'   => 'success',
				'redirect' => $charge['authorize_uri'],
			);
		}

		return $this->payment_failed(
			sprintf(
				__( 'Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise' ),
				$order_id
			)
		);
	}

	/**
	 * @return void
	 */
	public function callback() {
		$order_id = isset( $_GET['order_id'] ) ? sanitize_text_field( $_GET['order_id'] ) : '';
		$order    = $this->load_order( $order_id );
		if ( ! $order ) {
			$message = __(
				'<strong>We cannot validate your payment result:</strong><br/>
				 Note that your payment might already has been processed.<br/>
				 Please contact our support team if you have any questions.',
				'omise'
			);
			wc_add_notice( wp_kses( $message, array( 'br' => array(), 'strong' => array() ) ), 'error' );
			wp_redirect( wc_get_checkout_url() );
			exit;
		}

		$order->add_order_note( __( 'Omise: Validating the payment result..', 'omise' ) );

		try {
			$charge = OmiseCharge::retrieve( $order->get_transaction_id() );

			if ( self::STATUS_FAILED === $charge['status'] ) {
				throw new Exception( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
			}

			if ( self::STATUS_SUCCESSFUL === $charge['status'] && $charge['paid'] ) {
				$message = __( 'Omise: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' );
				$order->add_order_note(
					sprintf(
						wp_kses( $message, array( 'br' => array() ) ),
						$order->get_total(),
						$order->get_order_currency()
					)
				);

				$order->payment_complete();
				WC()->cart->empty_cart();
				wp_redirect( $order->get_checkout_order_received_url() );
				exit;
			}

			if ( self::STATUS_PENDING === $charge['status'] && ! $charge['paid'] ) {
				$message = __(
					'Omise: The payment has been processing.<br/>
					 Due to the payment provider, this may take some time to process.<br/>
					 Please do a manual \'Sync Payment Status\' action from the <strong>Order Actions</strong> panel or check the payment status directly at Omise dashboard again later.',
					'omise'
				);
				$order->add_order_note( wp_kses( $message, array( 'br' => array(), 'strong' => array() ) ) );
				$order->update_status( 'on-hold' );
				wp_redirect( $order->get_checkout_order_received_url() );
				exit;
			}

			$message = __(
				'Note that your payment might already has been processed.<br/>
				 Please contact our support team if you have any questions.',
				'omise'
			);
			throw new Exception( $message );
		} catch ( Exception $e ) {
			$message = __( 'It seems we\'ve been unable to process your payment properly:<br/>%s', 'omise' );
			wc_add_notice(
				sprintf(
					wp_kses( $message, array( 'br' => array() ) ),
					$e->getMessage()
				),
				'error'
			);

			$order->add_order_note(
				sprintf(
					wp_kses( __( 'Omise: Payment failed.<br/>%s', 'omise' ), array( 'br' => array() ) ),
					$e->getMessage()
				)
			);

			$order->update_status( 'failed' );
			wp_redirect( wc_get_checkout_url() );
			exit;
		}

		wp_die( 'Access denied', 'Access Denied', array( 'response' => 401 ) );
	}
}
