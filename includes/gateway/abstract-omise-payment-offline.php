<?php

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Omise_Payment_Offline' ) ) {
    return;
}

require_once dirname( __FILE__ ) . '/class-omise-payment.php';

/**
 * @since 4.0
 */
abstract class Omise_Payment_Offline extends Omise_Payment
{
	use Charge_Request_Builder;

	protected $enabled_processing_notification = true;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @inheritdoc
	 */
	public function process_payment( $order_id ) {
		return $this->process_upa_checkout_session_payment( $order_id );
	}

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
			return $this->payment_failed( $charge );
		}

		if ( self::STATUS_PENDING === $charge['status'] ) {
			$order->update_status( 'on-hold', sprintf( __( 'Omise: Awaiting %s to be paid.', 'omise' ), $this->title ) );
			$order->update_meta_data( 'is_omise_payment_resolved', 'yes' );
			$order->save();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);
		}

		return $this->payment_failed( null,
			sprintf(
				__( 'Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise' ),
				$order_id
			)
		);
	}
}
