<?php

use PHPUnit\Framework\TestCase;

class RequestHelperTest extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../../../../../../includes/libraries/omise-plugin/helpers/request.php';
    }

    /**
     * @dataProvider get_client_ip_data_provider
     * @covers RequestHelper
     */
    public function test_get_client_ip($serverArrKeyToTest)
    {
        $ipHeaderKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach($ipHeaderKeys as $ipHeaderKey) {
            if ($ipHeaderKey !== $serverArrKeyToTest) {
                $_SERVER[$ipHeaderKey] = null;
            }
        }

        $res = RequestHelper::get_client_ip();
        $this->assertEquals($_SERVER[$serverArrKeyToTest], $res);
    }

    /**
	 * Data provider for toSubunitReturnCorrectFormat
	 */
	public function get_client_ip_data_provider()
	{
        return [
            ['HTTP_CLIENT_IP'],
            ['HTTP_X_FORWARDED_FOR'],
            ['HTTP_X_FORWARDED'],
            ['HTTP_FORWARDED_FOR'],
            ['HTTP_FORWARDED'],
            ['REMOTE_ADDR'],
        ];
    }
}
