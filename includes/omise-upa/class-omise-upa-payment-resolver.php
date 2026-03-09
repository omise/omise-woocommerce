<?php

defined( 'ABSPATH' ) || exit;

class Omise_UPA_Payment_Resolver {
	const STATE_SUCCESSFUL = 'successful';
	const STATE_FAILED     = 'failed';
	const STATE_PENDING    = 'pending';

	/**
	 * UPA/charge statuses that represent terminal failure states.
	 *
	 * @var string[]
	 */
	private static $terminal_failed_statuses = array(
		'failed',
		'expired',
		'reversed',
		'cancelled',
		'canceled',
	);

	/**
	 * @var Omise_UPA_Client
	 */
	private $upa_client;

	/**
	 * @param Omise_UPA_Client|null $upa_client
	 *
	 * @throws Exception
	 */
	public function __construct( $upa_client = null ) {
		$this->upa_client = $upa_client ? $upa_client : $this->create_client();
	}

	/**
	 * Resolve order payment state by retrieving UPA session and Omise charge.
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function resolve( $order ) {
		$session_id = $order->get_meta( Omise_UPA_Session_Service::META_SESSION_ID );
		if ( empty( $session_id ) ) {
			throw new Exception( __( 'Unable to validate payment status. Please contact support.', 'omise' ) );
		}

		$session = $this->upa_client->get_session( $session_id );
		$status  = strtolower( isset( $session['status'] ) ? (string) $session['status'] : '' );
		$payments = isset( $session['payments'] ) && is_array( $session['payments'] ) ? $session['payments'] : array();

		$selected_payment = $this->pick_payment( $payments, $status );
		if ( empty( $selected_payment ) || empty( $selected_payment['charge_id'] ) ) {
			return array(
				'state'   => self::STATE_PENDING,
				'session' => $session,
				'payment' => $selected_payment,
				'charge'  => null,
			);
		}

		$charge = $this->retrieve_charge_with_retry( $selected_payment['charge_id'] );
		$state  = $this->determine_state( $status, $selected_payment, $charge );

		return array(
			'state'   => $state,
			'session' => $session,
			'payment' => $selected_payment,
			'charge'  => $charge,
		);
	}

	/**
	 * @return Omise_UPA_Client
	 *
	 * @throws Exception
	 */
	private function create_client() {
		$settings = Omise_Setting::instance();

		return new Omise_UPA_Client(
			$settings->get_upa_api_base_url(),
			$settings->secret_key()
		);
	}

	/**
	 * @param string $session_status
	 * @param array  $payment
	 * @param array  $charge
	 *
	 * @return string
	 */
	private function determine_state( $session_status, $payment, $charge ) {
		$payment_status = strtolower( isset( $payment['status'] ) ? (string) $payment['status'] : '' );
		$charge_status  = strtolower( isset( $charge['status'] ) ? (string) $charge['status'] : '' );

		$is_charge_successful = OmisePluginHelperCharge::isPaid( $charge ) || OmisePluginHelperCharge::isAuthorized( $charge );
		if ( $is_charge_successful ) {
			return self::STATE_SUCCESSFUL;
		}

		$is_failed = $this->is_terminal_failed_status( $session_status )
			|| $this->is_terminal_failed_status( $payment_status )
			|| $this->is_terminal_failed_status( $charge_status )
			|| OmisePluginHelperCharge::isFailed( $charge );
		if ( $is_failed ) {
			return self::STATE_FAILED;
		}

		return self::STATE_PENDING;
	}

	/**
	 * @param string $status
	 *
	 * @return bool
	 */
	private function is_terminal_failed_status( $status ) {
		return in_array( strtolower( (string) $status ), self::$terminal_failed_statuses, true );
	}

	/**
	 * @param array  $payments
	 * @param string $session_status
	 *
	 * @return array|null
	 */
	private function pick_payment( $payments, $session_status ) {
		if ( empty( $payments ) ) {
			return null;
		}

		foreach ( $payments as $payment ) {
			$payment_status = strtolower( isset( $payment['status'] ) ? (string) $payment['status'] : '' );
			if ( self::STATE_SUCCESSFUL === $payment_status && ! empty( $payment['charge_id'] ) ) {
				return $payment;
			}
		}

		foreach ( $payments as $payment ) {
			$payment_status = strtolower( isset( $payment['status'] ) ? (string) $payment['status'] : '' );
			if ( $this->is_terminal_failed_status( $payment_status ) && ! empty( $payment['charge_id'] ) ) {
				return $payment;
			}
		}

		foreach ( $payments as $payment ) {
			$payment_status = strtolower( isset( $payment['status'] ) ? (string) $payment['status'] : '' );
			if ( $payment_status === $session_status && ! empty( $payment['charge_id'] ) ) {
				return $payment;
			}
		}

		foreach ( $payments as $payment ) {
			if ( ! empty( $payment['charge_id'] ) ) {
				return $payment;
			}
		}

		return end( $payments );
	}

	/**
	 * @param string $charge_id
	 *
	 * @return OmiseCharge
	 *
	 * @throws Exception
	 */
	private function retrieve_charge_with_retry( $charge_id ) {
		$max_attempts = 3;

		for ( $attempt = 1; $attempt <= $max_attempts; $attempt++ ) {
			try {
				return OmiseCharge::retrieve( $charge_id );
			} catch ( Exception $e ) {
				if ( $attempt >= $max_attempts ) {
					throw $e;
				}

				usleep( ( 200 + (int) wp_rand( 0, 120 ) ) * 1000 );
			}
		}

		throw new Exception( __( 'Unable to validate payment status. Please contact support.', 'omise' ) );
	}
}
