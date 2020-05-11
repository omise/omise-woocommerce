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
	public function result( $order_id, $order, $charge ) {
		if ( self::STATUS_FAILED === $charge['status'] ) {
			return $this->payment_failed( Omise()->translate( $charge['failure_message'] ) . ' (code: ' . $charge['failure_code'] . ')' );
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
}
