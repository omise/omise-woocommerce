<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/class-omise-unit-test.php';

class Omise_Util_Test extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../../omise-util.php';
	}

	/**
	 * @test
	 */
	public function get_platform_type_android_phone()
	{
		$userAgent = 'Mozilla/5.0 (Linux; Android 7.0; SM-G930VC Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36';
		$expectedOutput = 'ANDROID';
		$this->assertEquals( $expectedOutput, Omise_Util::get_platform_type( $userAgent ) );
	}

	/**
	 * @test
	 */
	public function get_platform_type__ios_phone()
	{
		$userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1';
		$expectedOutput = 'IOS';
		$this->assertEquals( $expectedOutput, Omise_Util::get_platform_type( $userAgent ) );
	}

	/**
	 * @test
	 */
	public function get_platform_type__osx_desktop()
	{
		$userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9';
		$expectedOutput = null;
		$this->assertEquals( $expectedOutput, Omise_Util::get_platform_type( $userAgent ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_get_webhook_url()
	{
		if (!function_exists('get_rest_url')) {
            function get_rest_url() {
                return 'https://abc.com/wp-json/omise/webhooks';
            }
        }

		$webhookUrl = Omise_Util::get_webhook_url();
		$this->assertEquals( 'https://abc.com/wp-json/omise/webhooks', $webhookUrl );
	}
}
