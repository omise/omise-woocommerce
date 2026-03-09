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

		if ( ! function_exists( 'wp_parse_url' ) ) {
			function wp_parse_url( $url, $component = -1 ) {
				return parse_url( $url, $component );
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
	public function testGetUpaApiBaseUrlUsesEnvHostWhenValid()
	{
		putenv( 'OMISE_UPA_API_BASE_URL=checkout-page.staging-omise.co' );
		$setting = $this->create_setting( 'no' );

		$this->assertSame(
			'https://checkout-page.staging-omise.co/api',
			$setting->get_upa_api_base_url()
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGetUpaApiBaseUrlUsesEnvHostWhenFullUrlIsProvided()
	{
		putenv( 'OMISE_UPA_API_BASE_URL=https://checkout-page.omise.co/api' );
		$setting = $this->create_setting( 'yes' );

		$this->assertSame(
			'https://checkout-page.omise.co/api',
			$setting->get_upa_api_base_url()
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testGetUpaApiBaseUrlFallsBackToDefaultForInvalidEnvHost()
	{
		putenv( 'OMISE_UPA_API_BASE_URL=example.com' );
		$setting = $this->create_setting( 'no' );

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
}
