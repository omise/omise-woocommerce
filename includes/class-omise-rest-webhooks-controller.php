<?php
defined( 'ABSPATH' ) || die( 'No direct script access allowed.' );

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
	const ENDPOINT_ORDER_STATUS = 'order-status';

	/**
	 * @var string
	 */
	const RETURN_TRUE = '__return_true';

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
				'permission_callback' => self::RETURN_TRUE
			)
		);

		register_rest_route(
			self::ENDPOINT_NAMESPACE,
			'/' . self::ENDPOINT_ORDER_STATUS,
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'callback_get_order_status' ),
				'permission_callback' => self::RETURN_TRUE,
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
	public function callback_get_order_status( $request ) {
		$nonce = $request->get_param( '_nonce' );
		$order_key = $request->get_param( 'key' );

		if ( ! wp_verify_nonce( $nonce, 'get_order_status_' . $order_key ) ) {
			return new WP_Error( 'omise_rest_invalid_nonce', __( 'Invalid nonce.', 'omise' ), [ 'status' => 403 ] );
		}

		$order_id = wc_get_order_id_by_order_key( $order_key );
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return new WP_Error( 'omise_rest_order_not_found', __( 'Order not found.', 'omise' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( [ 'status' => $order->get_status() ] );
	}
}
