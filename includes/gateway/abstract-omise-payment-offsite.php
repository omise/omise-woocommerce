<?php
defined('ABSPATH') or die('No direct script access allowed.');

require_once dirname(__FILE__) . '/class-omise-payment.php';

/**
 * @since 3.10
 */
abstract class Omise_Payment_Offsite extends Omise_Payment
{
	use Charge_Request_Builder;

	/**
	 * @inheritdoc
	 */
	public function payment_fields() {
		parent::payment_fields();
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
	public function result($order_id, $order, $charge)
	{
		if (self::STATUS_FAILED === $charge['status']) {
			return $this->payment_failed($charge);
		}

		if (self::STATUS_PENDING === $charge['status']) {
			$order->add_order_note(sprintf(__('Omise: Redirecting buyer to %s', 'omise'), esc_url($charge['authorize_uri'])));

			return array(
				'result'   => 'success',
				'redirect' => $charge['authorize_uri'],
			);
		}

		return $this->payment_failed(null,
			sprintf(
				__('Please feel free to try submitting your order again, or contact our support team if you have any questions (Your temporary order id is \'%s\')', 'omise'),
				$order_id
			)
		);
	}

	/**
	 * Add validation to select bank
	 */
	public function check_bank_selected($fields, $errors)
	{
		$paymentMethodsToCheckBankSelected = ['omise_duitnow_obw', 'omise_fpx'];
		$source_bank = isset($_POST['source']['bank']) ? $_POST['source']['bank'] : '';
		if (empty($source_bank) && in_array($_POST['payment_method'], $paymentMethodsToCheckBankSelected)) {
			foreach ($errors->get_error_codes() as $code) {
				$errors->remove($code);
			}
			$errors->add('validation', __('Please select bank below', 'omise'));
		}
	}

	/**
	 * Override charge() method in the child class if the payment method requires
	 * more data than received from build_charge_request()
	 */
	public function charge($order_id, $order)
	{
		$requestData = $this->build_charge_request(
			$order_id, $order, $this->source_type, $this->id . "_callback"
		);
		return OmiseCharge::create($requestData);
	}
}
