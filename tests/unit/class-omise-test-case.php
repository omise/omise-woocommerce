<?php
use PHPUnit\Framework\TestCase;

abstract class Omise_Test_Case extends TestCase {
	use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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
