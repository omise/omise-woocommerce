<?php

require_once __DIR__ . '/../class-omise-unit-test.php';
require_once __DIR__ . '/gateway/bootstrap-test-setup.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Capability_Test extends Bootstrap_Test_Setup
{
	private $omiseSettingMock;

	private $omiseHttpExecutorMock;

	/**
	 * setup add_action and do_action before the test run
	 */
	protected function setUp(): void
	{
		parent::setUp();

		Mockery::mock('Omise_Payment_Offsite');
		require_once __DIR__ . '/../../../includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
		require_once __DIR__ . '/../../../includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';
		require_once __DIR__ . '/../../../includes/class-omise-capability.php';
		require_once __DIR__ . '/../../../includes/gateway/class-omise-payment-truemoney.php';
		$this->omiseSettingMock = Mockery::mock('alias:Omise_Setting');
		$this->omiseHttpExecutorMock = Mockery::mock('overload:OmiseHttpExecutor');

		$this->omiseSettingMock->shouldReceive('instance')->andReturn($this->omiseSettingMock);
		$this->omiseSettingMock->shouldReceive('public_key')->andReturn('pkey_xxx');
		$this->omiseSettingMock->shouldReceive('secret_key')->andReturn('skey_xxx');
	}

	/**
	 * @dataProvider retrieve_data_provider
	 * @covers Omise_Capability
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

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');
		$expected && $omiseCapabilityMock->shouldReceive('retrieve')->once();

		$result = Omise_Capability::retrieve();

		if ($expected) {
			$this->assertEquals('Omise_Capability', get_class($result));
		} else {
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
	 * @dataProvider truemoney_source_provider
	 */
	public function test_get_truemoney_method_returns_correct_value($name, $expected)
	{
		require_once __DIR__ . '/../../../includes/libraries/omise-php/lib/omise/OmiseCapability.php';

		Brain\Monkey\Functions\expect('is_admin')
			->with('123')
			->andReturn(true);

		Brain\Monkey\Functions\expect('is_checkout')
			->with('123')
			->andReturn(true);

		Brain\Monkey\Functions\expect('is_wc_endpoint_url')
			->with('123')
			->andReturn(false);

		$this->omiseHttpExecutorMock
			->shouldReceive('execute')
			->once()
			->andReturn(load_fixture('omise-capability-get'));

		$capability = Omise_Capability::retrieve();

		$result = $capability->get_truemoney_method($name);

		if ($expected) {
			$this->assertIsObject($result);
			$this->assertEquals($name, $result->name);
		} else {
			$this->assertNull($result);
		}
	}

	public function truemoney_source_provider()
	{
		return [ ['abc', false], ['truemoney', true], ['truemoney_jumpapp', true] ];
	}

	/**
	 * @dataProvider ajax_call_to_store_api_provider
	 * @covers Omise_Capability
	 */
	public function test_ajax_call_to_store_api_calls_omise_capability_api($request, $query_vars, $server_request_uri, $expected)
	{
		if ($request || $query_vars || $server_request_uri) {
			$wp = new stdClass();
			$wp->request = $request;
			$wp->query_vars = $query_vars;
			$GLOBALS['wp'] = $wp;
		}
		Brain\Monkey\Functions\expect('home_url')
			->andReturn('/');
		Brain\Monkey\Functions\expect('wp_doing_ajax')
			->andReturn(false);

		$_SERVER['REQUEST_URI'] = '/';
		if ($server_request_uri) {
			$_SERVER['REQUEST_URI'] = $server_request_uri;
		}

		$capability = new Omise_Capability;
		$result = $capability::isFromCheckoutPage();
		$this->assertEquals($expected, $result);
	}

	public function ajax_call_to_store_api_provider()
	{
		return [
			[null, null, null, false], // empty to test empty wp
			['wp-json/wc/store/v1/batch', [], null, true],
			['wp-json/wc/store/v1/batch', ['rest_route' => '/wc/store/v1/batch'], null, true],
			['', ['rest_route' => '/wc/store/v1/batch'], null, true],
			['', '', '/other/checkout', true],
			['', '', '/checkout/other', false],
			['', '', '/checkout?ewe=323', true],
		];
	}

	private function refresh($instance, $values, $clear = false)
	{
			if ($clear) {
					$instance->_values = [];
			}

			$instance->_values = $instance->_values ?: [];
			$values = $values ?: [];

			$instance->_values = array_merge($instance->_values, $values);
	}
}
