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

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @inheritdoc
	 */
	public function process_payment( $order_id ) {
		if ( ! Omise_Setting::instance()->is_upa_enabled() ) {
			return parent::process_payment( $order_id );
		}

		if ( ! $this->load_order( $order_id ) ) {
			return $this->invalid_order( $order_id );
		}

		if ( ! Omise_UPA_Feature_Flag::is_enabled_for_order( $this, $this->order() ) ) {
			return parent::process_payment( $order_id );
		}

		$this->order->add_order_note( sprintf( __( 'Omise: Processing a payment with %s', 'omise' ), $this->method_title ) );
		$this->order->add_meta_data( 'is_omise_payment_resolved', 'no', true );
		$this->order->save();

		try {
			return Omise_UPA_Session_Service::create_checkout_session( $this, $order_id, $this->order );
		} catch ( Exception $e ) {
			return $this->payment_failed( null, $e->getMessage() );
		}
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
