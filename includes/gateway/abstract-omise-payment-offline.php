<?php

defined( 'ABSPATH' ) || exit;

require_once dirname( __FILE__ ) . '/class-omise-payment.php';

/**
 * @since 4.0
 */
abstract class Omise_Payment_Offline extends Omise_Payment {
	/**
	 * A string of Omise Source's type
	 * (e.g. paynow or bill_payment_tesco_lotus).
	 *
	 * @var string
	 */
	protected $source_type;

	/**
	 * @inheritdoc
	 */
	public function build_charge_params() {
		return array_merge( $this->charge_params_default(), array(
			'source' => array( 'type' => $this->source_type )
		) );
	}

	/**
	 * @inheritdoc
	 */
	public function result( $order_id, $order, $charge ) {
		if ( self::STATUS_FAILED === $charge['status'] ) {
			return $this->payment_failed( $charge['failure_message'] . ' (code: ' . $charge['failure_code'] . ')' );
		}

		if ( self::STATUS_PENDING === $charge['status'] ) {
			$order->update_status( 'on-hold', sprintf( __( 'Omise: Awaiting %s to be paid.', 'omise' ), $this->title ) );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
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
