<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_MyAccount_Test extends Omise_Test_Case {
	protected function setUp(): void {
		parent::setUp();

		Monkey\Functions\stubs(
			[
				'wp_kses' => null,
				'plugins_url' => null,
				'admin_url' => null,
			]
		);

		$wc = Mockery::mock( 'WC' );
		$wc->shouldReceive( 'plugin_url' )->andReturn( '' );
		Monkey\Functions\expect( 'WC' )->andReturn( $wc );

		$omisePaymentMock = Mockery::mock( 'overload:Omise_Payment' );
		$omisePaymentMock->shouldReceive( 'init_settings' );
		$omisePaymentMock->shouldReceive( 'get_option' );

		require_once PLUGIN_PATH . '/includes/gateway/traits/charge-request-builder-trait.php';
		require_once PLUGIN_PATH . '/includes/gateway/traits/sync-order-trait.php';
		require_once PLUGIN_PATH . '/includes/gateway/abstract-omise-payment-base-card.php';
		require_once PLUGIN_PATH . '/includes/gateway/class-omise-payment-creditcard.php';

		load_plugin();
	}

	public function test_register_omise_my_account_scripts() {
		$current_user = Mockery::mock( 'WP_User' );
		$current_user->ID = 1;
		$current_user->test_omise_customer_id = 'cust_test_123';
		$current_user->user_email = 'johndoe@example.com';

		$setting = $this->mockOmiseSetting( 'pkey_test_123', 'skey_test_123' );
		$setting->shouldReceive( 'is_test' )->andReturn( true );
		Monkey\Functions\stubs(
			[
				'is_user_logged_in' => false,
				'wp_get_current_user' => $current_user,
			]
		);

		Monkey\Functions\expect( 'wp_enqueue_script' )
		->once()
		->with( 'omise-js', Omise::OMISE_JS_LINK, [ 'jquery' ], WC_VERSION, true );

		Monkey\Functions\expect( 'wp_enqueue_script' )
		->once()
		->with(
			'embedded-js',
			'/assets/javascripts/omise-embedded-card.js',
			[],
			OMISE_WOOCOMMERCE_PLUGIN_VERSION,
			true
		);

		Monkey\Functions\expect( 'wp_enqueue_script' )
		->once()
		->with(
			'omise-myaccount-card-handler',
			'/assets/javascripts/omise-myaccount-card-handler.js',
			[ 'omise-js' ],
			WC_VERSION,
			true
		);

		Monkey\Functions\expect( 'wp_localize_script' )
		->once()
		->with(
			'omise-myaccount-card-handler',
			'omise_params',
			Mockery::subset(
				[
					'key' => 'pkey_test_123',
					'account_email' => 'johndoe@example.com',
				]
			)
		);

		Omise_MyAccount::get_instance()->register_omise_my_account_scripts();
	}
}
