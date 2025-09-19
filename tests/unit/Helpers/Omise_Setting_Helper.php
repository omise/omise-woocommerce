<?php
namespace Omise\Tests\Helpers;

use Mockery;

trait Omise_Setting_Helper {
	/**
	 * @deprecated Use mock_omise_setting() instead.
	 * This method is kept for backward compatibility and will be removed in the future.
	 * FIXME: Refactor existing codebase to use snake case for consistency, then remove this function.
	 */
	protected function mockOmiseSetting( $pkey, $skey ) {
		return $this->mock_omise_setting( $pkey, $skey );
	}

	protected function mock_omise_setting( $pkey, $skey ) {
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
