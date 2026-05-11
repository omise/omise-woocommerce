<?php

use Brain\Monkey;

require_once __DIR__ . '/../../class-omise-unit-test.php';

class Omise_UPA_Back_Navigation_Guard_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-session-service.php';
		require_once __DIR__ . '/../../../../includes/omise-upa/class-omise-upa-back-navigation-guard.php';
	}

	protected function tearDown(): void {
		$_GET = array();
		parent::tearDown();
	}

	public function test_render_outputs_guard_script_on_paid_upa_order_received_page() {
		$_GET[ Omise_UPA_Back_Navigation_Guard::QUERY_PARAM ] = '1';

		Monkey\Functions\stubs(
			array(
				'is_admin'            => false,
				'wp_doing_ajax'       => false,
				'is_checkout'         => true,
				'is_wc_endpoint_url'  => true,
				'sanitize_text_field' => function( $value ) {
					return $value;
				},
				'wp_unslash'          => function( $value ) {
					return $value;
				},
				'get_query_var'       => function( $key ) {
					return 'order-received' === $key ? 44 : null;
				},
				'wp_json_encode'      => function( $value ) {
					return json_encode( $value );
				},
			)
		);

		$order = Mockery::mock( 'WC_Order' );
		$order->shouldReceive( 'get_meta' )
			->once()
			->with( Omise_UPA_Session_Service::META_FLOW )
			->andReturn( Omise_UPA_Session_Service::FLOW_OFFSITE );
		$order->shouldReceive( 'is_paid' )
			->once()
			->andReturn( true );

		Monkey\Functions\expect( 'wc_get_order' )
			->once()
			->with( 44 )
			->andReturn( $order );
		Monkey\Functions\expect( 'apply_filters' )
			->once()
			->with( 'omise_upa_enable_back_navigation_guard', true, $order )
			->andReturn( true );

		ob_start();
		Omise_UPA_Back_Navigation_Guard::render();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'omise-upa-back-navigation-guard', $output );
		$this->assertStringContainsString( 'Back navigation to previous payment page is disabled', $output );
	}

	public function test_render_outputs_nothing_when_guard_query_flag_is_missing() {
		Monkey\Functions\stubs(
			array(
				'is_admin'           => false,
				'wp_doing_ajax'      => false,
				'is_checkout'        => true,
				'is_wc_endpoint_url' => true,
			)
		);

		ob_start();
		Omise_UPA_Back_Navigation_Guard::render();
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}
}
