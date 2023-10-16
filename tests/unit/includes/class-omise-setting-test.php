<?php

use PHPUnit\Framework\TestCase;

class Omise_Setting_Test extends TestCase
{
    public function setUp(): void
	{
		require_once __DIR__ . '/../../../includes/class-omise-setting.php';
	}

    /**
     * @runInSeparateProcess
     */
    public function testIsDynamicWebhookEnabled()
    {
        if (!function_exists('get_option')) {
            function get_option() {
                return false;
            }
        }

        $setting = new Omise_Setting();

        $result = $setting->is_dynamic_webhook_enabled();

        // by default, it should be false
        $this->assertFalse($result);
    }
}
