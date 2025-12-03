<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Test extends TestCase {

	private Omise $model;

	/**
	 * setup add_action and do_action before the test run
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\expect( 'add_action' )->andReturn( null );
		Monkey\Functions\expect( 'do_action' )->andReturn( null );

		require_once __DIR__ . '/../../omise-woocommerce.php';
		$this->model = Omise::instance();
	}

	/**
	 * close mockery after test cases are done
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Making sure that when FeaturesUtil class do not exist,
	 * it doesn't throw any error
	 */
	public function test_when_features_util_class_do_not_exist() {
		$this->model->enable_hpos();
		$this->assertFalse( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) );
	}

	/**
	 * Making sure that when FeaturesUti class exist,
	 * it doesn't throw any error and the 'declare_compatibility' method should be called once
	 */
	public function test_when_features_util_class_exist() {
		$featuresUtilMock = Mockery::mock( 'alias:\Automattic\WooCommerce\Utilities\FeaturesUtil' );
		$featuresUtilMock->shouldReceive( 'declare_compatibility' )->twice();
		$this->model->enable_hpos();
		$this->assertTrue( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) );
	}

	public function test_omise_admin_notice_outputs_notice_element_on_omise_screen() {
		Monkey\Functions\stubs(
			[
				'get_current_screen' => ( (object) [
					'id' => 'toplevel_page_omise',
				] ),
			]
		);

		ob_start();
		$this->model->omise_admin_notice();
		$output = ob_get_clean();

		$this->assertMatchesRegularExpression( '/<div class=\'notice notice-warning is-dismissible\'>.*<\/div>/', $output );
		$this->assertMatchesRegularExpression( '/Our plugin now fully supports the WooCommerce Blocks!/', $output );
	}

	public function test_omise_admin_notice_does_not_output_anything_on_non_omise_screen() {
		Monkey\Functions\stubs(
			[
				'get_current_screen' => ( (object) [
					'id' => 'dashboard',
				] ),
			]
		);

		ob_start();
		$this->model->omise_admin_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	public function test_upgrade_plugin_updates_omise_version() {
		$currentVersion = '7.1.0';
		$updateVersion = '7.2.0';

		Monkey\Functions\expect( 'get_option' )
			->with( 'omise_version' )
			->andReturn( $currentVersion );

		Monkey\Functions\expect( 'update_option' )
			->once()
			->with( 'omise_version', $updateVersion )
			->andReturnTrue();

		$this->model->version = $updateVersion;
		$this->model->upgrade_plugin();

		// Monkey expectations are validated in tearDown
		$this->assertTrue( true );
	}

	public function test_upgrade_plugin_does_nothing_if_version_is_the_same() {
		$currentVersion = '7.1.0';
		$updateVersion = '7.1.0';

		Monkey\Functions\expect( 'get_option' )
			->with( 'omise_version' )
			->andReturn( $currentVersion );

		Monkey\Functions\expect( 'update_option' )->never();

		$this->model->version = $updateVersion;
		$this->model->upgrade_plugin();

		// Monkey expectations are validated in tearDown
		$this->assertTrue( true );
	}

	public function test_upgrade_plugin_updates_rabbit_linepay_title_after_v7() {
		$currentVersion = '7.0.0';
		$updateVersion = '7.1.0';
		$currentConfig = [
			'title' => 'Rabbit LINE Pay',
			'description' => '',
			'enabled' => 'yes',
		];

		Monkey\Functions\expect( 'get_option' )
			->andReturnUsing(
				function ( $option_name, $default = false ) use ( $currentVersion, $currentConfig ) {
					if ( $option_name === 'omise_version' ) {
						return $currentVersion;
					}
					if ( $option_name === 'woocommerce_omise_rabbit_linepay_settings' ) {
						return $currentConfig;
					}
					return $default;
				}
			);

		Monkey\Functions\expect( 'update_option' )
			->once()
			->with( 'omise_version', $updateVersion )
			->andReturnTrue();
		Monkey\Functions\expect( 'update_option' )
			->once()
			->with(
				'woocommerce_omise_rabbit_linepay_settings', [
					...$currentConfig,
					'title' => 'LINE Pay',
				]
			)
			->andReturnTrue();

		$this->model->version = $updateVersion;
		$this->model->upgrade_plugin();

		// Monkey expectations are validated in tearDown
		$this->assertTrue( true );
	}

	public function test_upgrade_plugin_does_not_update_rabbit_linepay_title_for_custom_title() {
		$currentVersion = '7.0.0';
		$updateVersion = '7.1.0';
		$currentConfig = [
			'title' => 'Custom LINE Pay Title',
			'description' => '',
			'enabled' => 'yes',
		];

		Monkey\Functions\expect( 'get_option' )
			->andReturnUsing(
				function ( $option_name, $default = false ) use ( $currentVersion, $currentConfig ) {
					if ( $option_name === 'omise_version' ) {
						return $currentVersion;
					}
					if ( $option_name === 'woocommerce_omise_rabbit_linepay_settings' ) {
						return $currentConfig;
					}
					return $default;
				}
			);

		Monkey\Functions\expect( 'update_option' )
			->once()
			->with( 'omise_version', $updateVersion )
			->andReturnTrue();
		Monkey\Functions\expect( 'update_option' )
			->never()
			->with( 'woocommerce_omise_rabbit_linepay_settings', Mockery::any() );

		$this->model->version = $updateVersion;
		$this->model->upgrade_plugin();

		// Monkey expectations are validated in tearDown
		$this->assertTrue( true );
	}

	public function test_upgrade_plugin_does_not_update_rabbit_linepay_title_without_linepay_config() {
		$currentVersion = '7.0.0';
		$updateVersion = '7.1.0';

		Monkey\Functions\expect( 'get_option' )
			->andReturnUsing(
				function ( $option_name, $default = false ) use ( $currentVersion ) {
					if ( $option_name === 'omise_version' ) {
						return $currentVersion;
					}
					if ( $option_name === 'woocommerce_omise_rabbit_linepay_settings' ) {
						return false;
					}
					return $default;
				}
			);

		Monkey\Functions\expect( 'update_option' )
			->once()
			->with( 'omise_version', $updateVersion )
			->andReturnTrue();
		Monkey\Functions\expect( 'update_option' )
			->never()
			->with( 'woocommerce_omise_rabbit_linepay_settings', Mockery::any() );

		$this->model->version = $updateVersion;
		$this->model->upgrade_plugin();

		// Monkey expectations are validated in tearDown
		$this->assertTrue( true );
	}
}
