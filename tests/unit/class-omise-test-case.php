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

	protected function mockOmiseSetting( $pkey, $skey ) {
		$omiseSettingMock = Mockery::mock( 'alias:Omise_Setting' );

		$omiseSettingMock->allows(
			[
				'instance' => $omiseSettingMock,
				'public_key' => $pkey,
				'secret_key' => $skey,
				'is_dynamic_webhook_enabled' => false,
			]
		);
		$omiseSettingMock->shouldReceive( 'get_settings' )->andReturn( [] )->byDefault();

		return $omiseSettingMock;
	}
}
