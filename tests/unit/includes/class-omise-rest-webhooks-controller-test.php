<?php

require_once __DIR__ . '/../class-omise-unit-test.php';
require_once __DIR__ . '/gateway/bootstrap-test-setup.php';

use Brain\Monkey;

class Omise_Rest_Webhooks_Controller_Test extends Bootstrap_Test_Setup {

	private $controller;

	protected function setUp(): void {
		require_once __DIR__ . '/../../../includes/class-omise-rest-webhooks-controller.php';

		$this->controller = new Omise_Rest_Webhooks_Controller();
	}

	public function test_rest_route_order_registered() {
		Mockery::namedMock( 'WP_REST_Server', WP_REST_Server_Stub::class );

		Monkey\Functions\expect( 'register_rest_route' )
			->once()
			->with(
				'omise', '/order-status', Mockery::on(
					function ( $args ) {
						return $args['methods'] === 'GET'
						&& $args['callback'][1] === 'callback_get_order_status'
						&& $args['permission_callback'] === '__return_true';
					}
				)
			);

		$this->controller->register_routes();
	}

	public function test_rest_route_order_get_order_status_returns_current_order_status() {
		$order_key = 'wc_order_kSwj6Gcnut4dU';
		$request = Mockery::mock( 'WP_REST_Request' );
		$request->shouldReceive( 'get_param' )->with( '_nonce' )->andReturn( '20285962135ae' );
		$request->shouldReceive( 'get_param' )->with( 'key' )->andReturn( $order_key );

		Monkey\Functions\expect( 'wp_verify_nonce' )
			->with( '20285962135ae', 'get_order_status_' . $order_key )
			->andReturn( true );
		$this->mockWcOrder( $order_key, 'processing' );

		Monkey\Functions\expect( 'rest_ensure_response' )
			->with(
				Mockery::on(
					function ( $response ) {
						return $response['status'] === 'processing';
					}
				)
			);

		$this->controller->callback_get_order_status( $request );
	}

	public function test_rest_route_order_get_order_status_returns_error_on_invalid_nonce() {
		$request = Mockery::mock( 'WP_REST_Request' );
		$request->shouldReceive( 'get_param' )->with( '_nonce' )->andReturn( 'f8624748c48bb' );
		$request->shouldReceive( 'get_param' )->with( 'key' )->andReturn( 'wc_order_022de71f05c42' );

		Monkey\Functions\expect( 'wp_verify_nonce' )->andReturn( false );

		$error = $this->controller->callback_get_order_status( $request );

		$this->assertEquals( 'omise_rest_invalid_nonce', $error->code );
		$this->assertEquals( 'Invalid nonce.', $error->message );
		$this->assertEquals( 403, $error->data['status'] );
	}

	public function test_rest_route_order_get_order_status_returns_error_on_invalid_order_id() {
		$request = Mockery::mock( 'WP_REST_Request' );
		$request->shouldReceive( 'get_param' )->with( '_nonce' )->andReturn( '8985449df3da0' );
		$request->shouldReceive( 'get_param' )->with( 'key' )->andReturn( 'wc_order_022de71f05c42' );

		Monkey\Functions\expect( 'wp_verify_nonce' )->andReturn( true );
		Monkey\Functions\expect( 'wc_get_order_id_by_order_key' )->andReturn( 0 );
		Monkey\Functions\expect( 'wc_get_order' )->andReturn( false );

		$error = $this->controller->callback_get_order_status( $request );

		$this->assertEquals( 'omise_rest_order_not_found', $error->code );
		$this->assertEquals( 'Order not found.', $error->message );
		$this->assertEquals( 404, $error->data['status'] );
	}

	private function mockWcOrder( $order_key, $status ) {
		$order_id = random_int( 1, 1000 );
		$order = Mockery::mock( 'WC_Order' );
		$order->allows(
			[
				'get_status' => $status,
				'get_order_key' => $order_key,
				'get_id' => $order_id,
			]
		);

		Monkey\Functions\stubs(
			[
				'wc_get_order_id_by_order_key' => function ( $key ) use ( $order_key, $order_id ) {
					return $key === $order_key ? $order_id : null;
				},
				'wc_get_order' => function ( $id ) use ( $order_id, $order ) {
					return $id === $order_id ? $order : null;
				},
			]
		);

		return $order;
	}
}
