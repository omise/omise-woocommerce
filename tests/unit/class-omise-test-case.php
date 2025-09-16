<?php
use PHPUnit\Framework\TestCase;
use Omise\Tests\Helpers\Omise_Setting_Helper;

abstract class Omise_Test_Case extends TestCase {
	use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
	use Omise_Setting_Helper;

	protected function setUp(): void {
		parent::setUp();
		Brain\Monkey\setUp();
	}

	protected function tearDown(): void {
		Brain\Monkey\tearDown();
		Mockery::close();
		parent::tearDown();
	}
}
