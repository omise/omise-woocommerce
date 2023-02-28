<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

require_once dirname( __FILE__ ) . '/class-omise-payment.php';

/**
 * @since 4.22.0
 */
abstract class Omise_Payment_Base_Card extends Omise_Payment
{
	const PAYMENT_ACTION_AUTHORIZE         = 'manual_capture';
	const PAYMENT_ACTION_AUTHORIZE_CAPTURE = 'auto_capture';

    /**
	 * @inheritdoc
	 */
	public function charge($order_id, $order)
	{
		$token = isset( $_POST['omise_token'] ) ? wc_clean( $_POST['omise_token'] ) : '';
		$card_id = isset( $_POST['card_id'] ) ? wc_clean( $_POST['card_id'] ) : '';

		if (empty($token) && empty($card_id)) {
			throw new Exception(__( 'Please select an existing card or enter new card information.', 'omise'));
		}

		$user = $order->get_user();
		$omise_customer_id = $this->is_test() ? $user->test_omise_customer_id : $user->live_omise_customer_id;

		// Saving card.
		if (isset($_POST['omise_save_customer_card']) && !empty($_POST['omise_save_customer_card']) && empty($card_id)) {
			$cardDetails = $this->saveCard($omise_customer_id, $token, $order_id, $user->ID);
			$omise_customer_id = $cardDetails['customer_id'];
			$card_id = $cardDetails['card_id'];
		}

		$data = $this->prepareChargeData($order_id, $order, $omise_customer_id, $card_id, $token);
		return OmiseCharge::create($data);
	}

	/**
	 * Prepare request data to create a charge
	 * @param string $order_id
	 * @param object $order
	 * @param string $omise_customer_id
	 * @param string $card_id
	 * @param string $token
	 */
	private function prepareChargeData($order_id, $order, $omise_customer_id, $card_id, $token)
	{
		$currency = $order->get_currency();
		$data = [
			'amount' => Omise_Money::to_subunit($order->get_total(), $currency),
			'currency' => $currency,
			'description' => apply_filters(
				'omise_charge_params_description',
				'WooCommerce Order id ' . $order_id,
				$order
			),
			'return_uri' => $this->getRedirectUrl('omise_callback', $order_id, $order),
			'metadata' => $this->getMetadata($order_id, $order)
		];

		if (!empty($omise_customer_id) && ! empty($card_id)) {
			$data['customer'] = $omise_customer_id;
			$data['card'] = $card_id;
		} else {
			$data['card'] = $token;
		}

		// Set capture status (otherwise, use API's default behaviour)
		if (self::PAYMENT_ACTION_AUTHORIZE_CAPTURE === $this->payment_action) {
			$data['capture'] = true;
		} else if (self::PAYMENT_ACTION_AUTHORIZE === $this->payment_action) {
			$data['capture'] = false;
		}

		return $data;
	}

	/**
	 * Saving card
	 * 
	 * @param string $omise_customer_id
	 * @param string $token
	 * @param string $order_id
	 * @param string $user_id
	*/
	public function saveCard($omise_customer_id, $token, $order_id, $user_id)
	{
		if (empty($token)) {
			throw new Exception(__(
				'Unable to process the card. Please make sure that the information is correct, or contact our support team if you have any questions.', 'omise'
			));
		}

		try {
			$customer = new Omise_Customer;
			$customer_data = [
				"description" => "WooCommerce customer " . $user_id,
				"card" => $token
			];

			if (empty($omise_customer_id)) {
				$customer_data = $customer->create($user_id, $order_id, $customer_data);

				return [
					'customer_id' => $customer_data['customer_id'],
					'card_id' => $customer_data['card_id']
				];
			}

			try {
				$customer->get($omise_customer_id);
				$customerCard = new OmiseCustomerCard;
				$card = $customerCard->create($omise_customer_id, $token);

				return [
					'customer_id' => $omise_customer_id,
					'card_id' => $card['id']
				];
			} catch(\Exception $e) {
				$errors = $e->getOmiseError();

				if($errors['object'] === 'error' && strtolower($errors['code']) !== 'not_found') {
					throw $e;
				}

				// Saved customer ID is not found so we create a new customer and save the customer ID
				$customer_data = $customer->create($user_id, $order_id, $customer_data);
				return [
					'customer_id' => $customer_data['customer_id'],
					'card_id' => $customer_data['card_id']
				];
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @inheritdoc
	 */
	public function result( $order_id, $order, $charge ) {
		if ( Omise_Charge::is_failed( $charge ) ) {
			return $this->payment_failed( Omise_Charge::get_error_message( $charge ) );
		}

		// If 3-D Secure feature is enabled, redirecting user out to a 3rd-party credit card authorization page.
		if ( self::STATUS_PENDING === $charge['status'] && ! $charge['authorized'] && ! $charge['paid'] && ! empty( $charge['authorize_uri'] ) ) {
			$order->add_order_note(
				sprintf(
					__( 'Opn Payments: Processing a 3-D Secure payment, redirecting buyer to %s', 'omise' ),
					esc_url( $charge['authorize_uri'] )
				)
			);

			return array(
				'result'   => 'success',
				'redirect' => $charge['authorize_uri'],
			);
		}

		switch ( $this->payment_action ) {
			case self::PAYMENT_ACTION_AUTHORIZE:
				$success = Omise_Charge::is_authorized( $charge );
				if ( $success ) {
					$order->add_order_note(
						sprintf(
							wp_kses(
								__( 'Opn Payments: Payment processing.<br/>An amount of %1$s %2$s has been authorized', 'omise' ),
								array( 'br' => array() )
							),
							$order->get_total(),
							$order->get_currency()
						)
					);
					$this->order->update_meta_data( 'is_awaiting_capture', 'yes' );
					$order->payment_complete();
				}

				break;

			case self::PAYMENT_ACTION_AUTHORIZE_CAPTURE:
				$success = Omise_Charge::is_paid( $charge );
				if ( $success ) {
					$order->add_order_note(
						sprintf(
							wp_kses(
								__( 'Opn Payments: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' ),
								array( 'br' => array() )
							),
							$order->get_total(),
							$order->get_currency()
						)
					);
					$order->payment_complete();
				}

				break;

			default:
				// Default behaviour is, check if it paid first.
				$success = Omise_Charge::is_paid( $charge );

				// Then, check is authorized after if the first condition is false.
				if ( ! $success ) {
					$success = Omise_Charge::is_authorized( $charge );
				}
					
				break;
		}

		if ( ! $success ) {
			return $this->payment_failed(__(
				'Note that your payment may have already been processed. Please contact our support team if you have any questions.',
				'omise'
			));
		}

		// Remove cart
		WC()->cart->empty_cart();
		return array (
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		);
	}

    /**
	 * Register all required javascripts
	 */
	public function omise_scripts() {
		if ( is_checkout() && $this->is_available() ) {
			wp_enqueue_script(
				'omise-js',
				Omise::OMISE_JS_LINK,
				[ 'jquery' ],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);

			wp_enqueue_script(
				'embedded-js',
				plugins_url( '../../assets/javascripts/omise-embedded-card.js', __FILE__ ),
				[],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);

			wp_enqueue_script(
				'omise-payment-form-handler',
				plugins_url( '../../assets/javascripts/omise-payment-form-handler.js', __FILE__ ),
				[ 'omise-js' ],
				OMISE_WOOCOMMERCE_PLUGIN_VERSION,
				true
			);

			wp_localize_script(
				'omise-payment-form-handler',
				'omise_params',
				$this->getParamsForJS()
			);
		}
	}

	/**
	 * Parameters to be passed directly to the JavaScript file.
	 */
	public function getParamsForJS()
	{
		return [
			'key'                            => $this->public_key(),
			'required_card_name'             => __(
				"Cardholder's name is a required field",
				'omise'
			),
			'required_card_number'           => __(
				'Card number is a required field',
				'omise'
			),
			'required_card_expiration_month' => __(
				'Card expiry month is a required field',
				'omise'
			),
			'required_card_expiration_year'  => __(
				'Card expiry year is a required field',
				'omise'
			),
			'required_card_security_code'    => __(
				'Card security code is a required field',
				'omise'
			),
			'invalid_card'                   => __(
				'Invalid card.',
				'omise'
			),
			'no_card_selected'               => __(
				'Please select a card or enter a new one.',
				'omise'
			),
			'cannot_create_token'            => __(
				'Unable to proceed to the payment.',
				'omise'
			),
			'cannot_connect_api'             => __(
				'Currently, the payment provider server is undergoing maintenance.',
				'omise'
			),
			'retry_checkout'                 => __(
				'Please place your order again in a couple of seconds.',
				'omise'
			),
			'cannot_load_omisejs'            => __(
				'Cannot connect to the payment provider.',
				'omise'
			),
			'check_internet_connection'      => __(
				'Please make sure that your internet connection is stable.',
				'omise'
			),
			'expiration date cannot be in the past' => __(
				'expiration date cannot be in the past',
				'omise'
			),
			'expiration date cannot be in the past and number is invalid' => __(
				'expiration date cannot be in the past and number is invalid',
				'omise'
			),
			'expiration date cannot be in the past, number is invalid, and brand not supported (unknown)' => __(
				'expiration date cannot be in the past, number is invalid, and brand not supported (unknown)',
				'omise'
			),
			'number is invalid and brand not supported (unknown)' => __(
				'number is invalid and brand not supported (unknown)',
				'omise'
			),
			'expiration year is invalid, expiration date cannot be in the past, number is invalid, and brand not supported (unknown)' => __(
				'expiration year is invalid, expiration date cannot be in the past, number is invalid, and brand not supported (unknown)',
				'omise'
			),
			'expiration month is not between 1 and 12, expiration date is invalid, number is invalid, and brand not supported (unknown)' => __(
				'expiration month is not between 1 and 12, expiration date is invalid, number is invalid, and brand not supported (unknown)',
				'omise'
			)
		];
	}
}
