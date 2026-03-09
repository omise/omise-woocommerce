<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Callback {
	const PENDING_RECHECK_ACTION = 'omise_upa_recheck_pending_order';
	const PENDING_RECHECK_GROUP  = 'omise_upa_recheck';
	const META_RETRY_ATTEMPTS    = 'omise_upa_retry_attempts';

	/**
	 * Register UPA callback endpoints.
	 */
	public static function register_hooks() {
		add_action( 'woocommerce_api_' . Omise_UPA_Session_Service::COMPLETE_ENDPOINT, array( __CLASS__, 'complete' ) );
		add_action( 'woocommerce_api_' . Omise_UPA_Session_Service::CANCEL_ENDPOINT, array( __CLASS__, 'cancel' ) );
		add_action( self::PENDING_RECHECK_ACTION, array( __CLASS__, 'recheck_pending_order' ), 10, 2 );
	}

	/**
	 * Handle complete callback from UPA.
	 */
	public static function complete() {
		$order = self::get_order_from_request();

		if ( ! $order ) {
			self::redirect_to_checkout();
		}

		if ( ! self::is_valid_state( $order ) ) {
			self::handle_invalid_state( $order, __( 'Omise UPA: Invalid callback state.', 'omise' ) );
		}

		if ( $order->is_paid() ) {
			$order->update_meta_data( 'is_omise_payment_resolved', 'yes' );
			$order->update_meta_data( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
			Omise_UPA_State_Token::invalidate( $order );
			$order->save();
			self::redirect_to_thank_you( $order, true );
		}

		try {
			$resolver = new Omise_UPA_Payment_Resolver();
			$result   = $resolver->resolve( $order );
			$result   = self::retry_pending_resolution_inline( $order, $resolver, $result );

			switch ( $result['state'] ) {
				case Omise_UPA_Payment_Resolver::STATE_SUCCESSFUL:
					self::handle_successful_payment( $order, $result );
					break;
				case Omise_UPA_Payment_Resolver::STATE_FAILED:
					self::handle_failed_payment( $order, $result );
					break;
				default:
					self::handle_pending_payment( $order, $result );
					break;
			}
		} catch ( Exception $e ) {
			$order->add_order_note(
				sprintf(
					wp_kses( __( 'Omise UPA: Unable to validate payment result.<br/>%s', 'omise' ), array( 'br' => array() ) ),
					esc_html( $e->getMessage() )
				)
			);
			wc_add_notice( __( 'Unable to validate payment status. Please check your order later.', 'omise' ), 'error' );
			self::redirect_to_checkout();
		}
	}

	/**
	 * Handle cancel callback from UPA.
	 */
	public static function cancel() {
		$order = self::get_order_from_request();

		if ( ! $order ) {
			self::redirect_to_checkout();
		}

		if ( ! self::is_valid_state( $order ) ) {
			self::handle_invalid_state( $order, __( 'Omise UPA: Invalid cancellation callback state.', 'omise' ) );
		}

		$order->add_order_note( __( 'Omise UPA: Payment was cancelled by customer.', 'omise' ) );
		$order->update_status( 'cancelled' );
		$order->update_meta_data( 'is_omise_payment_resolved', 'yes' );
		$order->update_meta_data( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		Omise_UPA_State_Token::invalidate( $order );
		$order->save();

		wc_add_notice( __( 'Payment was cancelled. Please try again.', 'omise' ), 'error' );
		self::redirect_to_checkout();
	}

	/**
	 * Background recheck for orders still pending after UPA return callback.
	 *
	 * @param int $order_id
	 * @param int $attempt
	 */
	public static function recheck_pending_order( $order_id, $attempt = 1 ) {
		$order_id = absint( $order_id );
		if ( empty( $order_id ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( $order->is_paid() || 'yes' === $order->get_meta( Omise_UPA_Session_Service::META_RESOLVED ) ) {
			return;
		}

		if ( $order->has_status( array( 'failed', 'cancelled', 'refunded' ) ) ) {
			return;
		}

		try {
			$resolver = new Omise_UPA_Payment_Resolver();
			$result   = $resolver->resolve( $order );
		} catch ( Exception $e ) {
			if ( $attempt >= self::get_recheck_attempt_limit() ) {
				$order->add_order_note(
					sprintf(
						__( 'Omise UPA: Automatic status check failed after %1$d attempts: %2$s', 'omise' ),
						$attempt,
						esc_html( $e->getMessage() )
					)
				);
				$order->save();
				return;
			}

			self::schedule_pending_recheck( $order->get_id(), $attempt + 1 );
			return;
		}

		switch ( $result['state'] ) {
			case Omise_UPA_Payment_Resolver::STATE_SUCCESSFUL:
				self::handle_successful_payment( $order, $result, false );
				break;
			case Omise_UPA_Payment_Resolver::STATE_FAILED:
				self::handle_failed_payment( $order, $result, false );
				break;
			default:
				if ( $attempt >= self::get_recheck_attempt_limit() ) {
					$order->add_order_note( __( 'Omise UPA: Payment is still pending after automatic checks. Please use Sync Payment Status later.', 'omise' ) );
					$order->save();
					return;
				}

				self::schedule_pending_recheck( $order->get_id(), $attempt + 1 );
				break;
		}
	}

	/**
	 * @param WC_Order $order
	 * @param array    $result
	 * @param bool     $should_redirect
	 */
	private static function handle_successful_payment( $order, $result, $should_redirect = true ) {
		$charge = $result['charge'];
		self::set_transaction_id( $order, $charge );

		$order->payment_complete();
		$order->add_order_note(
			sprintf(
				wp_kses( __( 'Omise UPA: Payment successful.<br/>An amount of %1$s %2$s has been paid', 'omise' ), array( 'br' => array() ) ),
				$order->get_total(),
				$order->get_currency()
			)
		);
		$order->update_meta_data( 'is_omise_payment_resolved', 'yes' );
		$order->update_meta_data( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		$order->delete_meta_data( self::META_RETRY_ATTEMPTS );
		Omise_UPA_State_Token::invalidate( $order );
		$order->save();

		if ( $should_redirect && function_exists( 'WC' ) && WC()->cart ) {
			WC()->cart->empty_cart();
		}

		if ( $should_redirect ) {
			self::redirect_to_thank_you( $order, true );
		}
	}

	/**
	 * @param WC_Order $order
	 * @param array    $result
	 * @param bool     $should_redirect
	 */
	private static function handle_pending_payment( $order, $result, $should_redirect = true ) {
		if ( ! empty( $result['charge'] ) ) {
			self::set_transaction_id( $order, $result['charge'] );
		}

		$has_payment = ! empty( $result['payment'] ) && is_array( $result['payment'] );
		$message = $has_payment
			? __( 'Omise UPA: Payment is pending. We are waiting for final confirmation from the payment provider.', 'omise' )
			: __( 'Omise UPA: Payment session is still pending. Please check back in a moment.', 'omise' );

		$order->add_order_note( $message );
		$order->update_status( 'on-hold' );
		$order->update_meta_data( 'is_omise_payment_resolved', 'no' );
		$order->update_meta_data( Omise_UPA_Session_Service::META_RESOLVED, 'no' );
		$order->save();

		self::schedule_pending_recheck( $order->get_id(), 1 );

		if ( $should_redirect ) {
			self::redirect_to_thank_you( $order );
		}
	}

	/**
	 * @param WC_Order $order
	 * @param array    $result
	 * @param bool     $should_redirect
	 */
	private static function handle_failed_payment( $order, $result, $should_redirect = true ) {
		if ( ! empty( $result['charge'] ) ) {
			self::set_transaction_id( $order, $result['charge'] );
		}

		$failure_message = __( 'Payment failed', 'omise' );

		if ( ! empty( $result['charge'] ) ) {
			$failure_message = Omise()->translate( $result['charge']['failure_message'] );
			if ( ! empty( $result['charge']['failure_code'] ) ) {
				$failure_message .= sprintf( ' (code: %s)', $result['charge']['failure_code'] );
			}
		}

		if ( self::should_offer_retry( $order, $result ) ) {
			self::mark_retryable_failure( $order, $failure_message );

			if ( $should_redirect ) {
				wc_add_notice( __( 'Your payment could not be completed yet. Please try again.', 'omise' ), 'error' );
				self::redirect_to_order_pay( $order );
			}

			// Background recheck: order left as pending so buyer can retry from order-pay page.
			return;
		}

		$status = self::resolve_failed_order_status( $result );

		$order->add_order_note( Omise_WC_Order_Note::get_payment_failed_note( null, $failure_message ) );
		$order->update_status( $status );
		$order->update_meta_data( 'is_omise_payment_resolved', 'yes' );
		$order->update_meta_data( Omise_UPA_Session_Service::META_RESOLVED, 'yes' );
		Omise_UPA_State_Token::invalidate( $order );
		$order->save();

		if ( $should_redirect ) {
			wc_add_notice( sprintf( __( 'It seems we have been unable to process your payment properly: %s', 'omise' ), $failure_message ), 'error' );
			self::redirect_to_checkout();
		}
	}

	/**
	 * @return WC_Order|null
	 */
	private static function get_order_from_request() {
		$order_id = isset( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : null;

		if ( empty( $order_id ) ) {
			return null;
		}

		return wc_get_order( $order_id );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	private static function is_valid_state( $order ) {
		$state = self::get_state_from_request();

		return Omise_UPA_State_Token::validate( $order, $state );
	}

	/**
	 * Retrieve and normalize state from callback request.
	 * Some providers append their own query params with a second '?'
	 * which can end up inside the state value. Keep only the token portion.
	 *
	 * @return string|null
	 */
	private static function get_state_from_request() {
		if ( ! isset( $_GET['omise_upa_state'] ) ) {
			return null;
		}

		$state = wp_unslash( $_GET['omise_upa_state'] );

		if ( ! is_string( $state ) ) {
			return null;
		}

		$state = trim( $state );
		$state = explode( '?', $state )[0];

		if ( empty( $state ) ) {
			return null;
		}

		return sanitize_text_field( $state );
	}

	/**
	 * Handle invalid callback state.
	 * If order is already resolved, this is treated as a replay and redirected
	 * to a safe terminal page without altering order state.
	 *
	 * @param WC_Order $order
	 * @param string   $note
	 */
	private static function handle_invalid_state( $order, $note ) {
		if ( self::is_callback_replay( $order ) ) {
			self::redirect_to_checkout();
		}

		$order->add_order_note( $note );
		self::redirect_to_checkout();
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	private static function is_callback_replay( $order ) {
		$resolved = $order->get_meta( Omise_UPA_Session_Service::META_RESOLVED );
		$state    = $order->get_meta( Omise_UPA_Session_Service::META_STATE );

		return 'yes' === $resolved && empty( $state );
	}

	/**
	 * @param WC_Order $order
	 * @param array    $charge
	 */
	private static function set_transaction_id( $order, $charge ) {
		if ( empty( $charge['id'] ) ) {
			return;
		}

		if ( $order->get_transaction_id() === $charge['id'] ) {
			return;
		}

		$order->set_transaction_id( $charge['id'] );
	}

	/**
	 * @param int $order_id
	 * @param int $attempt
	 */
	private static function schedule_pending_recheck( $order_id, $attempt ) {
		$attempt = max( 1, absint( $attempt ) );

		if ( $attempt > self::get_recheck_attempt_limit() ) {
			return;
		}

		if ( ! function_exists( 'WC' ) || ! WC()->queue() ) {
			return;
		}

		$delay_seconds = self::get_recheck_delay_seconds( $attempt );
		$run_at        = time() + $delay_seconds;

		WC()->queue()->schedule_single(
			$run_at,
			self::PENDING_RECHECK_ACTION,
			array(
				'order_id' => absint( $order_id ),
				'attempt'  => $attempt,
			),
			self::PENDING_RECHECK_GROUP
		);
	}

	/**
	 * @return int
	 */
	private static function get_recheck_attempt_limit() {
		$limit = (int) apply_filters( 'omise_upa_recheck_attempt_limit', 96 );

		return $limit > 0 ? $limit : 96;
	}

	/**
	 * @param int $attempt
	 *
	 * @return int
	 */
	private static function get_recheck_delay_seconds( $attempt ) {
		$attempt = max( 1, absint( $attempt ) );
		$default = $attempt <= 6 ? 15 * $attempt : 900;
		$delay   = (int) apply_filters( 'omise_upa_recheck_delay_seconds', $default, $attempt );

		return $delay > 0 ? $delay : $default;
	}

	/**
	 * Perform a few short retries in the callback request to absorb eventual consistency
	 * between UPA session status and charge status updates.
	 *
	 * @param WC_Order                   $order
	 * @param Omise_UPA_Payment_Resolver $resolver
	 * @param array                      $result
	 *
	 * @return array
	 */
	private static function retry_pending_resolution_inline( $order, $resolver, $result ) {
		if ( empty( $result ) || ! is_array( $result ) ) {
			return $result;
		}

		if ( Omise_UPA_Payment_Resolver::STATE_PENDING !== $result['state'] ) {
			return $result;
		}

		$attempt_limit = (int) apply_filters( 'omise_upa_inline_pending_retry_attempts', 3, $order, $result );
		$attempt_limit = $attempt_limit > 1 ? $attempt_limit : 1;
		$delay_ms      = (int) apply_filters( 'omise_upa_inline_pending_retry_delay_ms', 1000, $order, $result );
		$delay_ms      = $delay_ms > 0 ? $delay_ms : 1000;

		for ( $attempt = 2; $attempt <= $attempt_limit; $attempt++ ) {
			usleep( $delay_ms * 1000 );

			try {
				$result = $resolver->resolve( $order );
			} catch ( Exception $e ) {
				continue;
			}

			if ( Omise_UPA_Payment_Resolver::STATE_PENDING !== $result['state'] ) {
				return $result;
			}
		}

		return $result;
	}

	/**
	 * @param WC_Order $order
	 * @param array    $result
	 *
	 * @return bool
	 */
	private static function should_offer_retry( $order, $result ) {
		if ( ! self::is_retryable_failure( $result ) ) {
			return false;
		}

		$attempts = absint( $order->get_meta( self::META_RETRY_ATTEMPTS ) );
		$limit    = self::get_retry_attempt_limit();

		return $attempts < $limit;
	}

	/**
	 * @param array $result
	 *
	 * @return bool
	 */
	private static function is_retryable_failure( $result ) {
		if ( empty( $result['charge'] ) || ! is_array( $result['charge'] ) ) {
			return false;
		}

		$failure_code = isset( $result['charge']['failure_code'] ) ? strtolower( trim( (string) $result['charge']['failure_code'] ) ) : '';
		if ( empty( $failure_code ) ) {
			return false;
		}

		$default_codes = array(
			'processing_error',
			'temporarily_unavailable',
			'provider_unavailable',
			'provider_timeout',
			'timeout',
			'network_error',
			'internal_error',
		);
		$retryable_codes = (array) apply_filters( 'omise_upa_retryable_failure_codes', $default_codes, $result );
		$retryable_codes = array_map(
			function( $code ) {
				return strtolower( trim( (string) $code ) );
			},
			$retryable_codes
		);

		return in_array( $failure_code, $retryable_codes, true );
	}

	/**
	 * @return int
	 */
	private static function get_retry_attempt_limit() {
		$limit = (int) apply_filters( 'omise_upa_retry_attempt_limit', 1 );

		return $limit > 0 ? $limit : 1;
	}

	/**
	 * @param WC_Order $order
	 * @param string   $failure_message
	 */
	private static function mark_retryable_failure( $order, $failure_message ) {
		$attempts = absint( $order->get_meta( self::META_RETRY_ATTEMPTS ) ) + 1;
		$limit    = self::get_retry_attempt_limit();

		$order->add_order_note(
			sprintf(
				__( 'Omise UPA: Retryable payment failure (%1$s). Buyer can retry payment (%2$d/%3$d).', 'omise' ),
				$failure_message,
				$attempts,
				$limit
			)
		);
		$order->update_meta_data( self::META_RETRY_ATTEMPTS, (string) $attempts );
		$order->update_meta_data( 'is_omise_payment_resolved', 'no' );
		$order->update_meta_data( Omise_UPA_Session_Service::META_RESOLVED, 'no' );
		Omise_UPA_State_Token::invalidate( $order );
		$order->update_status( 'pending' );
		$order->save();
	}

	/**
	 * @param array $result
	 *
	 * @return string
	 */
	private static function resolve_failed_order_status( $result ) {
		$charge_status = '';
		if ( ! empty( $result['charge'] ) && is_array( $result['charge'] ) && ! empty( $result['charge']['status'] ) ) {
			$charge_status = strtolower( trim( (string) $result['charge']['status'] ) );
		}

		$cancelled_like_statuses = array( 'expired', 'reversed', 'cancelled', 'canceled' );
		if ( in_array( $charge_status, $cancelled_like_statuses, true ) ) {
			return 'cancelled';
		}

		return 'failed';
	}

	/**
	 * @param WC_Order $order
	 */
	private static function redirect_to_thank_you( $order, $enable_back_guard = false ) {
		$redirect_url = $order->get_checkout_order_received_url();

		if ( $enable_back_guard ) {
			$redirect_url = add_query_arg( 'omise_upa_guard', '1', $redirect_url );
		}

		nocache_headers();
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * @param WC_Order $order
	 */
	private static function redirect_to_order_pay( $order ) {
		$redirect_url = $order->get_checkout_payment_url( true );

		if ( empty( $redirect_url ) ) {
			$redirect_url = wc_get_checkout_url();
		}

		nocache_headers();
		wp_safe_redirect( $redirect_url );
		exit;
	}

	private static function redirect_to_checkout() {
		nocache_headers();
		wp_safe_redirect( wc_get_checkout_url() );
		exit;
	}
}
