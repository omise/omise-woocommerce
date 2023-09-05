<?php

use PHPUnit\Framework\TestCase;

class Omise_Test extends TestCase
{
	private Omise $model;

	/**
	 * setup add_action and do_action before the test run
	 */
	public function setUp(): void
	{
		if (!function_exists('add_action')) {
			function add_action()
			{
			}
		}
		if (!function_exists('do_action')) {
			function do_action()
			{
			}
		}
		require_once __DIR__ . '/../../omise-woocommerce.php';
		$this->model = Omise::instance();
	}

	/**
	 * close mockery after test cases are done
	 */
	public function tearDown(): void
	{
		Mockery::close();
	}

	/**
	 * Making sure that when FeaturesUtil class do not exist,
	 * it doesn't throw any error
	 */
	public function test_when_features_util_class_do_not_exist()
	{
		$this->model->enable_hpos();
		$this->assertFalse(class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class));
	}

	/**
	 * Making sure that when FeaturesUti class exist,
	 * it doesn't throw any error and the 'declare_compatibility' method should be called once
	 */
	public function test_when_features_util_class_exist()
	{
		$featuresUtilMock = Mockery::mock('alias:\Automattic\WooCommerce\Utilities\FeaturesUtil');
		$featuresUtilMock->shouldReceive('declare_compatibility')->once();
		$this->model->enable_hpos();
		$this->assertTrue(class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class));
	}
}
