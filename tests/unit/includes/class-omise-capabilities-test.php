<?php

use PHPUnit\Framework\TestCase;

class Omise_Capabilities_Test extends TestCase
{
	/**
	 * setup add_action and do_action before the test run
	 */
	public function setUp(): void
	{
		require_once __DIR__ . '/../../../includes/class-omise-capabilities.php';
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
