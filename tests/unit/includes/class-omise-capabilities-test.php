<?php

require_once __DIR__ . '/gateway/bootstrap-test-setup.php';

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Capabilities_Test extends Bootstrap_Test_Setup
{
	private $omiseSettingMock;

	private $omiseCapabilitiesMock;

	/**
	 * setup add_action and do_action before the test run
	 */
	public function setUp(): void
	{
		parent::setUp();

		Mockery::mock('Omise_Payment_Offsite');
		require_once __DIR__ . '/../../../includes/class-omise-capabilities.php';
		require_once __DIR__ . '/../../../includes/gateway/class-omise-payment-truemoney.php';
		$this->omiseSettingMock = Mockery::mock('alias:Omise_Setting');
		$this->omiseCapabilitiesMock = Mockery::mock('alias:OmiseCapabilities');
	}

	/**
	 * @dataProvider retrieve_data_provider
	 * @covers Omise_Capabilities
	 */
	public function test_retrieve_should_return_value_when_it_should_call_api($isCheckout, $isThankYouPage, $isAdmin, $adminPageName, $expected)
	{
		// assigning to global variable, so that we can use in child functions
		$GLOBALS['isCheckout'] = $isCheckout;
		$GLOBALS['isThankYouPage'] = $isThankYouPage;
		$GLOBALS['isAdmin'] = $isAdmin;

		// mocking page name
		$_GET['page'] = $adminPageName;

		Brain\Monkey\Functions\expect('is_admin')
			->with('123')
			->andReturn($GLOBALS['isAdmin']);
		Brain\Monkey\Functions\expect('is_checkout')
			->with('123')
			->andReturn($GLOBALS['isCheckout']);
		Brain\Monkey\Functions\expect('is_wc_endpoint_url')
			->with('123')
			->andReturn($GLOBALS['isThankYouPage']);

		if ($expected) {
			$this->omiseSettingMock->shouldReceive('instance')->andReturn($this->omiseSettingMock);
			$this->omiseSettingMock->shouldReceive('public_key')->andReturn('pkey_xxx');
			$this->omiseSettingMock->shouldReceive('secret_key')->andReturn('skey_xxx');
			$this->omiseCapabilitiesMock->shouldReceive('retrieve')->once();
			$result = Omise_Capabilities::retrieve();
			$this->assertEquals('Omise_Capabilities', get_class($result));
		} else {
			$result = Omise_Capabilities::retrieve();
			$this->assertEquals(null, $result);
		}
	}

	/**
	 * Data provider for toSubunitReturnCorrectFormat
	 */
	public function retrieve_data_provider()
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

	/**
	 * @test
	 */
	public function test_get_truemoney_backend_returns_null_when_invalid_payment_is_passed()
	{
		Brain\Monkey\Functions\expect('is_admin')
			->with('123')
			->andReturn(true);

		Brain\Monkey\Functions\expect('is_checkout')
			->with('123')
			->andReturn(true);

		Brain\Monkey\Functions\expect('is_wc_endpoint_url')
			->with('123')
			->andReturn(false);

		$capabilities = new Omise_Capabilities;
		$is_enabled = $capabilities->get_truemoney_backend('abc');
		$this->assertNull($is_enabled);
	}

	public function truemoney_source_provider()
	{
		return [ ['abc', false], ['truemoney', true], ['truemoney_jumpapp', true] ];
	}
}

class Omise_Payment_Truemoney_Stub
{
	/**
	 * Backends identifier
	 * @var string
	 */
	const WALLET = 'truemoney';
	const JUMPAPP = 'truemoney_jumpapp';
}
