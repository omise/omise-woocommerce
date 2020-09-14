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
	public function charge( $order_id, $order ) {
		$total    = $order->get_total();
		$currency = $order->get_currency();
		$metadata = array_merge(
			apply_filters( 'omise_charge_params_metadata', array(), $order ),
			array( 'order_id' => $order_id ) // override order_id as a reference for webhook handlers.
		);

		return OmiseCharge::create( array(
			'amount'      => Omise_Money::to_subunit( $total, $currency ),
			'currency'    => $currency,
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'source'      => array( 'type' => $this->source_type ),
			'metadata'    => $metadata
		) );
	}

	/**
	 * @inheritdoc
	 */
	public function result( $order_id, $order, $charge ) {
		if ( self::STATUS_FAILED === $charge['status'] ) {
			return $this->payment_failed( Omise()->translate( $charge['failure_message'] ) . ' (code: ' . $charge['failure_code'] . ')' );
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
		
		return $this->payment_failed(
			sprintf(
				__( 'Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise' ),
				$order_id
			)
		);
	}
}
