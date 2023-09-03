<?php

use PHPUnit\Framework\TestCase;

class Omise_Capabilities_Test extends TestCase
{
	private $omiseSettingMock;

	private $omiseCapabilitiesMock;

	/**
	 * setup add_action and do_action before the test run
	 */
	public function setUp(): void
	{
		require_once __DIR__ . '/../../../includes/class-omise-capabilities.php';
		$this->omiseSettingMock = Mockery::mock('alias:Omise_Setting');
		$this->omiseCapabilitiesMock = Mockery::mock('alias:OmiseCapabilities');
	}

	/**
	 * close mockery after test cases are done
	 */
	public function tearDown(): void
	{
		Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 * @covers Omise_Capabilities
	 */
	public function test_retrieve_should_return_null_when_it_should_not_call_api()
	{
		function is_admin() { return false; }
		function is_checkout() { return false; }
		function is_wc_endpoint_url($page) { return true; }

		$result = Omise_Capabilities::retrieve();
		$this->assertEquals(null, $result);
	}

	/**
	 * @runInSeparateProcess
	 * @covers Omise_Capabilities
	 */
	public function test_retrieve_should_return_value_when_it_should_call_api()
	{
		function is_admin() { return false; }
		function is_checkout() { return true; }
		function is_wc_endpoint_url($page) { return false; }

		$this->omiseSettingMock->shouldReceive('instance')->andReturn($this->omiseSettingMock);
		$this->omiseSettingMock->shouldReceive('public_key')->andReturn('pkey_xxx');
		$this->omiseSettingMock->shouldReceive('secret_key')->andReturn('skey_xxx');
		$this->omiseCapabilitiesMock->shouldReceive('retrieve')->once();

		$result = Omise_Capabilities::retrieve();

		$this->assertEquals('Omise_Capabilities', get_class($result));
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider should_call_api_data_provider
	 * @covers Omise_Capabilities
	 */
	public function test_should_call_capability_api($isCheckout, $isThankYouPage, $isAdmin, $adminPageName, $expected)
	{
		$_GET['page'] = $adminPageName;
		$result = Omise_Capabilities::shouldCallApi($isCheckout, $isThankYouPage, $isAdmin);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Data provider for toSubunitReturnCorrectFormat
	 */
	public function should_call_api_data_provider()
	{
		return [
			// checkout page and not thank you page
			[true, false, false, '', true],
			// checkout page and also thank you page
			[true, true, false, '', false],
			// omise setting page
			[true, true, true, 'omise', true],
			// other admin page
			[true, true, true, 'other-page', false],
			// non checkout page and also no-admin page
			[false, false, false, 'other-page', false],
			// non checkout page, non admin page
			[false, false, false, '', false],
		];
	}
}
