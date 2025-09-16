<?php
namespace Omise\Tests\Helpers;

use Mockery;

trait Omise_Setting_Helper {
	// FIXME: Rename function name to snake case for consistency.
	protected function mockOmiseSetting( $pkey, $skey ) {
		$omise_setting_mock = Mockery::mock( 'alias:Omise_Setting' );

		$omise_setting_mock->allows(
			[
				'instance' => $omise_setting_mock,
				'public_key' => $pkey,
				'secret_key' => $skey,
				'is_dynamic_webhook_enabled' => false,
			]
		);
		$omise_setting_mock->shouldReceive( 'get_settings' )->andReturn( [] )->byDefault();

		return $omise_setting_mock;
	}
}
