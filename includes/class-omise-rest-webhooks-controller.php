<?php
defined( 'ABSPATH' ) or die( 'No direct script access allowed.' );

if ( class_exists( 'Omise_Rest_Webhooks_Controller' ) ) {
	return;
}

class Omise_Rest_Webhooks_Controller {
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	const ENDPOINT_NAMESPACE = 'omise';

	/**
	 * @var string
	 */
	const ENDPOINT = 'webhooks';

	/**
	 * @var string
	 */
	const PAYNOW_CHARGE_STATUS_ENDPOINT = 'paynow-payment-status';

	/**
	 * Register the routes for webhooks.
	 */
	public function register_routes() {
		register_rest_route(
			self::ENDPOINT_NAMESPACE,
			'/' . self::ENDPOINT,
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'callback' ),
				'permission_callback' => '__return_true'
			)
		);

		register_rest_route(
			self::ENDPOINT_NAMESPACE,
			'/' . self::PAYNOW_CHARGE_STATUS_ENDPOINT,
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'callback_paynow_payment_status' ),
				'permission_callback' => '__return_true'
			)
		);
	}

	/**
	 * @param  WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function callback( $request ) {
		if ( 'application/json' !== $request->get_header( 'Content-Type' ) ) {
			return new WP_Error( 'omise_rest_wrong_header', __( 'Wrong header type.', 'omise' ), array( 'status' => 400 ) );
		}

		$body = json_decode( $request->get_body(), true );

		if ( 'event' !== $body['object'] ) {
			return new WP_Error( 'omise_rest_wrong_object', __( 'Wrong object type.', 'omise' ), array( 'status' => 400 ) );
		}

		$event = new Omise_Events;
		$event = $event->handle( $body['key'], $body['data'] );

		return rest_ensure_response( $event );
	}

	/**
	 * @param  WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function callback_paynow_payment_status($request) {
		$order_id = $request->get_param('order_id');
		$data['status'] =  false;
		if(isset( $order_id )) {
			$order = new WC_Order( $order_id );
			if(isset($order)) {
				$data['status'] =  $order->get_status();
			}
		}
		return rest_ensure_response( $data );
	}
}
