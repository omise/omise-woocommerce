<?php

defined( 'ABSPATH' ) || exit;

require_once dirname( __FILE__ ) . '/class-omise-payment.php';

/**
 * @since 4.0
 */
abstract class Omise_Payment_Offline extends Omise_Payment
{
	use Charge_Request_Builder;

	protected $enabled_processing_notification = true;

	/**
	 * @inheritdoc
	 */
	public function charge( $order_id, $order )
	{
		$requestData = $this->build_charge_request(
			$order_id, $order, $this->source_type
		);

		return OmiseCharge::create($requestData);
	}

	/**
	 * @inheritdoc
	 */
	public function result( $order_id, $order, $charge ) {
		if ( self::STATUS_FAILED === $charge['status'] ) {
			return $this->payment_failed( Omise()->translate( $charge['failure_message'] ) . ' (code: ' . $charge['failure_code'] . ')' );
		}

		if ( self::STATUS_PENDING === $charge['status'] ) {
			$order->update_status( 'on-hold', sprintf( __( 'Opn Payments: Awaiting %s to be paid.', 'omise' ), $this->title ) );
			$order->update_meta_data( 'is_omise_payment_resolved', 'yes' );
			$order->save();

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
