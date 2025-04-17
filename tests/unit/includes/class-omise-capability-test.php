<?php

require_once __DIR__ . '/../class-omise-unit-test.php';
require_once __DIR__ . '/gateway/bootstrap-test-setup.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Capability_Test extends Bootstrap_Test_Setup
{
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
		require_once __DIR__ . '/../../../includes/gateway/class-omise-payment-shopeepay.php';
	}

	/**
	 * @dataProvider retrieve_data_provider
	 * @covers Omise_Capability
	 */
	public function test_retrieve_returns_value_when_it_should_call_api($isCheckout, $isThankYouPage, $isAdmin, $adminPageName, $expected)
	{
		$this->mockOmiseSetting(['pkey_xxx'], skey: ['skey_xxx']);

		// assigning to global variable, so that we can use in child functions
		$GLOBALS['isCheckout'] = $isCheckout;
		$GLOBALS['isThankYouPage'] = $isThankYouPage;
		$GLOBALS['isAdmin'] = $isAdmin;

		// mocking page name
		$_GET['page'] = $adminPageName;

		Brain\Monkey\Functions\expect('is_admin')
			->andReturn($GLOBALS['isAdmin']);
		Brain\Monkey\Functions\expect('is_checkout')
			->andReturn($GLOBALS['isCheckout']);
		Brain\Monkey\Functions\expect('is_wc_endpoint_url')
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

	public function test_retrieve_returns_null_when_retrieve_capability_fails()
	{
		$this->enableApiCall(true);
		$this->mockOmiseSetting(['pkey_xxx'], skey: ['skey_xxx']);

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');
		$omiseCapabilityMock->shouldReceive('retrieve')->once()->andThrows(new Exception('Request failed'));
		Brain\Monkey\Functions\expect('error_log')->once()->with('Request failed');

		$result = Omise_Capability::retrieve();

		$this->assertNull($result);
	}

	/**
	 * @dataProvider retrieve_keys_data_provider
	 */
	public function test_retrieve_using_new_setting_keys_if_user_submit_form($sandbox)
	{
		$_POST = [
			'submit' => 'Save Settings',
			'omise_setting_page_nonce' => 1,
			'sandbox' => true,
			'test_public_key' => 'pkey_test_xxx',
			'test_private_key' => 'skey_test_xxx',
			'live_public_key' => 'pkey_xxx',
			'live_private_key' => 'skey_xxx',
		];

		if (!$sandbox) {
			unset($_POST['sandbox']);
		}

		Brain\Monkey\Functions\expect('wp_verify_nonce')->andReturn(true);
		Brain\Monkey\Functions\when('sanitize_text_field')->returnArg();
		$this->enableApiCall(true);

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');

		if ($sandbox) {
			$omiseCapabilityMock->shouldReceive('retrieve')->once()->with('pkey_test_xxx', 'skey_test_xxx');
		} else {
			$omiseCapabilityMock->shouldReceive('retrieve')->once()->with('pkey_xxx', 'skey_xxx');
		}

		Omise_Capability::retrieve();
	}

	public function retrieve_keys_data_provider()
	{
		return [[true], [false]];
	}

	public function test_retrieve_exits_when_new_setting_keys_are_from_suspicious_source()
	{
		$_POST = [
			'submit' => 'Save Settings',
			'live_public_key' => 'pkey_xxx',
			'live_private_key' => 'skey_xxx',
		];

		Brain\Monkey\Functions\expect('wp_verify_nonce')->andReturn(false);
		Brain\Monkey\Functions\expect('wp_die')->once();
		$this->enableApiCall(true);

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');
		$omiseCapabilityMock->shouldNotReceive('retrieve');

		Omise_Capability::retrieve();
	}

	public function test_retrieve_returns_existing_instance_without_extra_api_call_if_keys_not_changed()
	{
		$this->enableApiCall(true);
		$this->mockOmiseSetting(['pkey_xxx'], skey: ['skey_xxx']);

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');
		$omiseCapabilityMock->shouldReceive('retrieve')->once();

		Omise_Capability::retrieve();
		Omise_Capability::retrieve();
	}

	public function test_retrieve_returns_existing_instance_and_fetch_new_capability_if_keys_changed()
	{
		$this->enableApiCall(true);
		$this->mockOmiseSetting(['pkey_xxx', 'pkey_new'], ['pkey_xxx', 'skey_new']);

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');
		$omiseCapabilityMock->shouldReceive('retrieve')->twice();

		Omise_Capability::retrieve();
		Omise_Capability::retrieve();
	}

	public function test_retrieve_returns_null_when_it_should_not_call_api()
	{
		$this->enableApiCall(false);

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');
		$omiseCapabilityMock->shouldNotReceive('retrieve');

		$omiseCapability = Omise_Capability::retrieve();

		$this->assertNull($omiseCapability);
	}

	public function test_retrieve_returns_null_when_keys_not_set()
	{
		$this->enableApiCall(true);
		$this->mockOmiseSetting([''], skey: ['']);

		$omiseCapabilityMock = Mockery::mock('alias:OmiseCapability');
		$omiseCapabilityMock->shouldNotReceive('retrieve');

		$omiseCapability = Omise_Capability::retrieve();

		$this->assertNull($omiseCapability);
	}


	public function test_get_installment_methods_returns_payment_methods_with_name_starts_with_installment()
	{
		$this->mockCapabilityRetrieve();

		$installmentMethods = Omise_Capability::retrieve()->getInstallmentMethods();

		$this->assertIsArray($installmentMethods);
		$this->assertNotEmpty($installmentMethods);
		foreach ($installmentMethods as $method) {
			$this->assertStringStartsWith('installment_', $method->name);
		}
	}

	public function test_get_installment_methods_filtered_by_curreny_and_charge_amount()
	{
		$this->mockCapabilityRetrieve();

		$installmentMethods = Omise_Capability::retrieve()->getInstallmentMethods('THB', 10000);

		$this->assertIsArray($installmentMethods);
		$this->assertEmpty($installmentMethods);
	}

	public function test_get_payment_methods_returns_all_available_payment_methods()
	{
		$this->mockCapabilityRetrieve();

		$paymentMethods = Omise_Capability::retrieve()->getPaymentMethods();

		$this->assertIsArray($paymentMethods);
		$this->assertCount(45, $paymentMethods);

		$paymentMethod = $paymentMethods[0];
		$this->assertObjectHasProperty('object', $paymentMethod);
		$this->assertObjectHasProperty('name', $paymentMethod);
		$this->assertObjectHasProperty('currencies', $paymentMethod);
		$this->assertObjectHasProperty('card_brands', $paymentMethod);
		$this->assertObjectHasProperty('installment_terms', $paymentMethod);
		$this->assertObjectHasProperty('banks', $paymentMethod);
		$this->assertObjectHasProperty('provider', $paymentMethod);
	}

	public function test_get_payment_methods_filtered_by_currency()
	{
		$this->mockCapabilityRetrieve();

		$paymentMethods = Omise_Capability::retrieve()->getPaymentMethods('JPY');

		$this->assertIsArray($paymentMethods);
		$this->assertCount(1, $paymentMethods);
		$this->assertEquals('card', $paymentMethods[0]->name);
	}

	public function test_get_available_payment_methods_returns_all_available_payment_methods_with_tokenization()
	{
		$this->mockCapabilityRetrieve();

		$availablePaymentMethods = Omise_Capability::retrieve()->get_available_payment_methods();

		$this->assertIsArray($availablePaymentMethods);
		// Extra 2 methods from the tokenization method
		$this->assertCount(47, $availablePaymentMethods);
		$this->assertContains('card', $availablePaymentMethods);
		$this->assertContains('googlepay', $availablePaymentMethods);
		$this->assertContains('applepay', $availablePaymentMethods);
	}

	public function test_get_fpx_banks_returns_correct_method()
	{
		$this->mockCapabilityRetrieve();

		$fpx = Omise_Capability::retrieve()->getFPXBanks();

		$this->assertFalse($fpx);
	}

	public function test_get_tokenization_returns_correct_value()
	{
		$this->mockCapabilityRetrieve();

		$tokenizationMethods = Omise_Capability::retrieve()->getTokenizationMethods();

		$this->assertIsArray($tokenizationMethods);
		$this->assertCount(2, $tokenizationMethods);
		$this->assertContains('googlepay', $tokenizationMethods);
		$this->assertContains('applepay', $tokenizationMethods);
	}

	public function test_is_zero_interest_returns_correct_value()
	{
		$this->mockCapabilityRetrieve();

		$isZroInterest = Omise_Capability::retrieve()->is_zero_interest();

		$this->assertFalse($isZroInterest);
	}

	public function test_get_installment_min_limit_returns_correct_value()
	{
		$this->mockCapabilityRetrieve();

		$installmentMinLimit = Omise_Capability::retrieve()->getInstallmentMinLimit();

		$this->assertEquals(200000, $installmentMinLimit);
	}

	/**
	 * @dataProvider shopee_source_provider
	 */
	public function test_get_shopee_method_returns_correct_value($name, $expected)
	{
		$this->mockCapabilityRetrieve();

		$result = Omise_Capability::retrieve()->getShopeeMethod($name);

		if ($expected) {
			$this->assertIsObject($result);
			$this->assertEquals($name, $result->name);
		} else {
			$this->assertNull($result);
		}
	}

	public function shopee_source_provider()
	{
		return [['abc', false], ['shopeepay', true], ['shopeepay_jumpapp', true]];
	}

	/**
	 * @dataProvider truemoney_source_provider
	 */
	public function test_get_truemoney_method_returns_correct_value($name, $expected)
	{
		$this->mockCapabilityRetrieve();

		$result = Omise_Capability::retrieve()->get_truemoney_method($name);

		if ($expected) {
			$this->assertIsObject($result);
			$this->assertEquals($name, $result->name);
		} else {
			$this->assertNull($result);
		}
	}

	public function truemoney_source_provider()
	{
		return [['abc', false], ['truemoney', true], ['truemoney_jumpapp', true]];
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

	public function mockOmiseSetting($pkey, $skey)
	{
		$omiseSettingMock = Mockery::mock('alias:Omise_Setting');

		$omiseSettingMock->shouldReceive('instance')->andReturn($omiseSettingMock);
		$omiseSettingMock->shouldReceive('public_key')->andReturn(...$pkey);
		$omiseSettingMock->shouldReceive('secret_key')->andReturn(...$skey);

		return $omiseSettingMock;
	}

	private function mockCapabilityRetrieve()
	{
		$this->mockApiCall('omise-capability-get');
	}
}
