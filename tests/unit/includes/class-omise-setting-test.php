<?php

use PHPUnit\Framework\TestCase;

class Omise_Setting_Test extends TestCase
{
	protected function setUp(): void
	{
		require_once __DIR__ . '/../../../includes/class-omise-setting.php';
	}

	private function bootstrap_wordpress_functions() {
		if ( ! function_exists( 'get_option' ) ) {
			function get_option() {
				return false;
			}
		}

		if ( ! function_exists( 'sanitize_text_field' ) ) {
			function sanitize_text_field( $value ) {
				return $value;
			}
		}

		if ( ! function_exists( 'esc_html' ) ) {
			function esc_html( $value ) {
				return $value;
			}
		}
	}

	private function create_setting( $sandbox_mode = 'yes' ) {
		$this->bootstrap_wordpress_functions();
		$setting = new Omise_Setting();
		$setting->settings['sandbox'] = $sandbox_mode;

		return $setting;
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIsDynamicWebhookEnabled()
	{
		$setting = $this->create_setting();

		$result = $setting->is_dynamic_webhook_enabled();

		// by default, it should be false
		$this->assertFalse($result);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGetUpaApiBaseUrlUsesProductionHost()
	{
		$setting = $this->create_setting( 'no' );

		$this->assertSame(
			'https://checkout-page.omise.co/api',
			$setting->get_upa_api_base_url()
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGetUpaApiBaseUrlIgnoresLegacyEnvHostOverride()
	{
		putenv( 'OMISE_UPA_API_BASE_URL=checkout-page.evil.com' );
		$setting = $this->create_setting( 'yes' );

		$this->assertSame(
			'https://checkout-page.omise.co/api',
			$setting->get_upa_api_base_url()
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGetUpaApiBaseUrlFallsBackToProductionWhenEnvMissing()
	{
		putenv( 'OMISE_UPA_API_BASE_URL' );
		$setting = $this->create_setting( 'yes' );

		$this->assertSame(
			'https://checkout-page.omise.co/api',
			$setting->get_upa_api_base_url()
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIsUpaEnabledReturnsFalseWhenFeatureFlagIsMissing()
	{
		$setting = $this->create_setting();
		$setting->settings[ Omise_Setting::OPTION_ENABLE_UPA ] = 1;

		$this->assertFalse( $setting->is_upa_enabled() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIsUpaEnabledReturnsTrueWhenFeatureFlagAndMerchantToggleAreEnabled()
	{
		if ( ! defined( Omise_Setting::FEATURE_UPA_FLAG ) ) {
			define( Omise_Setting::FEATURE_UPA_FLAG, true );
		}

		$setting = $this->create_setting();
		$setting->settings[ Omise_Setting::OPTION_ENABLE_UPA ] = 'yes';

		$this->assertTrue( $setting->is_upa_enabled() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIsUpaFeatureFlagEnabledSupportsTruthyStringValues()
	{
		if ( ! defined( Omise_Setting::FEATURE_UPA_FLAG ) ) {
			define( Omise_Setting::FEATURE_UPA_FLAG, 'on' );
		}

		$setting = $this->create_setting();
		$this->assertTrue( $setting->is_upa_feature_flag_enabled() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIsUpaEnabledByMerchantCastsNumericFlag()
	{
		$setting = $this->create_setting();
		$setting->settings[ Omise_Setting::OPTION_ENABLE_UPA ] = 1;
		$this->assertTrue( $setting->is_upa_enabled_by_merchant() );

		$setting->settings[ Omise_Setting::OPTION_ENABLE_UPA ] = 0;
		$this->assertFalse( $setting->is_upa_enabled_by_merchant() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testIsTruthyHandlesSupportedTypes()
	{
		$setting = $this->create_setting();
		$method  = new ReflectionMethod( Omise_Setting::class, 'is_truthy' );
		$method->setAccessible( true );

		$this->assertTrue( $method->invoke( $setting, true ) );
		$this->assertFalse( $method->invoke( $setting, false ) );
		$this->assertTrue( $method->invoke( $setting, 1 ) );
		$this->assertFalse( $method->invoke( $setting, 2 ) );
		$this->assertTrue( $method->invoke( $setting, 'true' ) );
		$this->assertFalse( $method->invoke( $setting, 'false' ) );
		$this->assertFalse( $method->invoke( $setting, array( 1 ) ) );
	}
}
