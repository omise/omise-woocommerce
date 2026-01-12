<?php

use Brain\Monkey;

require_once __DIR__ . '/traits/mock-gateways.php';

class Omise_Block_Credit_Card_Test extends Omise_Test_Case {
	use MockPaymentGateways;

	public $obj;

	protected $omise_setting_mock;

	protected function setUp(): void {
		parent::setUp();
		$this->mockWcGateways();
		require_once __DIR__ . '/../../../../../includes/blocks/gateways/omise-block-credit-card.php';
		$this->omise_setting_mock = Mockery::mock( 'alias:Omise_Setting' );
		$this->obj = new Omise_Block_Credit_Card();

		Monkey\Functions\stubs(
			[
				'wc_string_to_bool' => function ( $val ) {
					return $val === 'yes';
				},
			]
		);
	}

	public function test_initialize() {
		Monkey\Functions\expect( 'get_option' )->andReturn( null );

		$this->obj->initialize();

		$reflection = new \ReflectionClass( $this->obj );
		$gateway_property = $reflection->getProperty( 'gateway' );
		$gateway_property->setAccessible( true );
		$gateway_val = $gateway_property->getValue( $this->obj );

		$this->assertEquals( 'object', gettype( $gateway_val ) );
	}

	public function test_is_active() {
		Monkey\Functions\expect( 'get_option' )
			->once()
			->with( 'woocommerce_omise_settings', [] )
			->andReturn(
				[
					'enabled' => 'yes',
				]
			);

		$this->obj->initialize();
		$is_active = $this->obj->is_active();

		$this->assertTrue( $is_active );
	}

	public function test_get_payment_method_data() {
		$mock_settings = [
			'title' => 'Credit Card',
			'description' => 'This is Credit Card payment method.',
			'enabled' => 'yes',
		];
		Monkey\Functions\expect( 'get_option' )
			->once()
			->with( 'woocommerce_omise_settings', [] )
			->andReturn( $mock_settings );
		Monkey\Functions\expect( 'get_locale' )->andReturn( 'th' );
		$this->omise_setting_mock->shouldReceive( 'instance' )->andReturn( $this->omise_setting_mock );
		$this->omise_setting_mock->shouldReceive( 'public_key' )->andReturn( 'pkey_xxx' );

		$this->obj->initialize();
		$data = $this->obj->get_payment_method_data();

		$this->assertEquals( 'omise', $data['name'] );
		$this->assertEquals( $mock_settings['title'], $data['title'] );
		$this->assertEquals( $mock_settings['description'], $data['description'] );
		$this->assertEquals( 'array', gettype( $data['features'] ) );
		$this->assertEquals( 'th', $data['locale'] );
		$this->assertEquals( 'pkey_xxx', $data['public_key'] );
		$this->assertEquals( true, $data['is_active'] );
	}

	public function test_get_payment_method_script_handles() {
		Monkey\Functions\stubs(
			[
				'plugins_url' => null,
				'plugin_dir_url' => '',
				'is_checkout' => true,
			]
		);
		Monkey\Functions\expect( 'get_option' )
			->once()
			->with( 'woocommerce_omise_settings', [] )
			->andReturn(
				[
					'enabled' => 'yes',
				]
			);
		// Expect the scripts are enqueued correctly.
		Monkey\Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'embedded-js',
				'../../../assets/javascripts/omise-embedded-card.js',
				[ 'omise-js' ],
				'9.1.0',
				true
			);
		Monkey\Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'omise-payments-blocks',
				'assets/js/build/credit_card.js',
				// The dependencies are defined in the actual script.
				// We just want to make sure 'embedded-js' is included.
				Mockery::contains( 'embedded-js' ),
				Mockery::any(),
				true
			);

		$this->obj->initialize();
		$result = $this->obj->get_payment_method_script_handles();

		$this->assertEquals( [ 'omise-payments-blocks' ], $result );
	}
}
