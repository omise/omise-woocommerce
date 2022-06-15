<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

require_once dirname( __FILE__ ) . '/class-omise-payment.php';

/**
 * @since 4.21
 */
abstract class Omise_Payment_Base_Card extends Omise_Payment {
	const PAYMENT_ACTION_AUTHORIZE         = 'manual_capture';
	const PAYMENT_ACTION_AUTHORIZE_CAPTURE = 'auto_capture';

    /**
	 * @inheritdoc
	 */
	public function charge( $order_id, $order ) {
		$token   = isset( $_POST['omise_token'] ) ? wc_clean( $_POST['omise_token'] ) : '';
		$card_id = isset( $_POST['card_id'] ) ? wc_clean( $_POST['card_id'] ) : '';

		if ( empty( $token ) && empty( $card_id ) ) {
			throw new Exception( __( 'Please select an existing card or enter new card information.', 'omise' ) );
		}

		$user              = $order->get_user();
		$omise_customer_id = $this->is_test() ? $user->test_omise_customer_id : $user->live_omise_customer_id;

		// Saving card.
		if ( isset( $_POST['omise_save_customer_card'] ) && empty( $card_id ) ) {
			if ( empty( $token ) ) {
				throw new Exception( __( 'Unable to process the card. Please make sure that the information is correct, or contact our support team if you have any questions.', 'omise' ) );
			}

			if ( ! empty( $omise_customer_id ) ) {
				try {
					// attach a new card to customer
					$customer = OmiseCustomer::retrieve( $omise_customer_id );
					$customer->update( array(
						'card' => $token
					) );

					$cards = $customer->cards( array(
						'limit' => 1,
						'order' => 'reverse_chronological'
					) );

					$card_id = $cards['data'][0]['id'];
				} catch (Exception $e) {
					throw new Exception( $e->getMessage() );
				}
			} else {
				$description   = "WooCommerce customer " . $user->ID;
				$customer_data = array(
					"description" => $description,
					"card"        => $token
				);

				$omise_customer = OmiseCustomer::create( $customer_data );

				if ( $omise_customer['object'] == "error" ) {
					throw new Exception( $omise_customer['message'] );
				}

				$omise_customer_id = $omise_customer['id'];
				if ( $this->is_test() ) {
					update_user_meta( $user->ID, 'test_omise_customer_id', $omise_customer_id );
				} else {
					update_user_meta( $user->ID, 'live_omise_customer_id', $omise_customer_id );
				}

				if ( 0 == sizeof( $omise_customer['cards']['data'] ) ) {
					throw new Exception(
						sprintf(
							wp_kses(
								__( 'Please note that you\'ve done nothing wrong - this is likely an issue with our store.<br/><br/>Feel free to try submitting your order again, or report this problem to our support team (Your temporary order id is \'%s\')', 'omise' ),
								array(
									'br' => array()
								)
							),
							$order_id
						)
					);
				}

				$cards   = $omise_customer->cards( array( 'order' => 'reverse_chronological' ) );
				$card_id = $cards['data'][0]['id']; //use the latest card
			}
		}

		$success    = false;
		$return_uri = add_query_arg(
			array(
				'wc-api'   => 'omise_callback',
				'order_id' => $order_id
			),
			home_url()
		);
		$data    = array(
			'amount'      => Omise_Money::to_subunit( $order->get_total(), $order->get_currency() ),
			'currency'    => $order->get_currency(),
			'description' => apply_filters( 'omise_charge_params_description', 'WooCommerce Order id ' . $order_id, $order ),
			'return_uri'  => $return_uri
		);

		if ( ! empty( $omise_customer_id ) && ! empty( $card_id ) ) {
			$data['customer'] = $omise_customer_id;
			$data['card']     = $card_id;
		} else {
			$data['card'] = $token;
		}

		// Set capture status (otherwise, use API's default behaviour)
		if ( self::PAYMENT_ACTION_AUTHORIZE_CAPTURE === $this->payment_action ) {
			$data['capture'] = true;
		} else if ( self::PAYMENT_ACTION_AUTHORIZE === $this->payment_action ) {
			$data['capture'] = false;
		}
		$metadata = apply_filters( 'omise_charge_params_metadata', array(), $order );

		$data['metadata'] = array_merge( $metadata, array(
			/** override order_id as a reference for webhook handlers **/
			/** backward compatible with WooCommerce v2.x series **/
			'order_id' => version_compare( WC()->version, '3.0.0', '>=' ) ? $order->get_id() : $order->id
		) );

		return OmiseCharge::create( $data );
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
					__( 'Omise: Processing a 3-D Secure payment, redirecting buyer to %s', 'omise' ),
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
								__( 'Omise: Payment processing.<br/>An amount of %1$s %2$s has been authorized', 'omise' ),
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
								__( 'Omise: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' ),
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
				if ( ! $success )
					$success = Omise_Charge::is_authorized( $charge );

				break;
		}

		if ( ! $success ) {
			return $this->payment_failed( __( 'Note that your payment may have already been processed. Please contact our support team if you have any questions.', 'omise' ) );
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
			wp_enqueue_script( 'omise-js', 'https://cdn.omise.co/omise.js', array( 'jquery' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );
			wp_enqueue_script( 'omise-payment-form-handler', plugins_url( '../../assets/javascripts/omise-payment-form-handler.js', __FILE__ ), array( 'omise-js' ), OMISE_WOOCOMMERCE_PLUGIN_VERSION, true );

			$omise_params = array(
				'key'                            => $this->public_key(),
				'required_card_name'             => __( 'Cardholder\'s name is a required field', 'omise' ),
				'required_card_number'           => __( 'Card number is a required field', 'omise' ),
				'required_card_expiration_month' => __( 'Card expiry month is a required field', 'omise' ),
				'required_card_expiration_year'  => __( 'Card expiry year is a required field', 'omise' ),
				'required_card_security_code'    => __( 'Card security code is a required field', 'omise' ),
				'invalid_card'                   => __( 'Invalid card.', 'omise' ),
				'no_card_selected'               => __( 'Please select a card or enter a new one.', 'omise' ),
				'cannot_create_token'            => __( 'Unable to proceed to the payment.', 'omise' ),
				'cannot_connect_api'             => __( 'Currently, the payment provider server is undergoing maintenance.', 'omise' ),
				'retry_checkout'                 => __( 'Please place your order again in a couple of seconds.', 'omise' ),
				'cannot_load_omisejs'            => __( 'Cannot connect to the payment provider.', 'omise' ),
				'check_internet_connection'      => __( 'Please make sure that your internet connection is stable.', 'omise' ),
			);

			wp_localize_script( 'omise-payment-form-handler', 'omise_params', $omise_params );
		}
	}
}
