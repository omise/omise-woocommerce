<?php

class Omise_Payment_Promptpay_Test extends Omise_Payment_Offline_Test
{
    public $mockOrder;
    public $mockWcDateTime;
    public $mockLocalizeScript;
    public $mockOmisePluginHelper;
    public $mockOmisePaymentOffline;
    public $mockOmiseCharge;
    public $mockFileGetContent;

    public function setUp(): void
    {
        parent::setUp();
        function wc_timezone_offset() {}
        function wp_create_nonce() {}
        function admin_url() {}

        $this->mockOrder = Mockery::mock();
        $this->mockLocalizeScript = Mockery::mock();
        $this->mockWcDateTime = Mockery::mock('overload:WC_DateTime');
        $this->mockOmisePluginHelper = Mockery::mock('overload:OmisePluginHelperWcOrder')->shouldIgnoreMissing();
        $this->mockOmisePaymentOffline = Mockery::mock('overload:Omise_Payment_Offline');
        $this->mockOmiseCharge = Mockery::mock('overload:OmiseCharge');
        $this->mockFileGetContent = Mockery::mock('overload:File_Get_Contents_Wrapper');

        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-promptpay.php';
    }

    /**
     * @test
     */
    public function textExpiresAtFieldIsPassedToJavascript()
    {
        $expiresAt = '2023-11-22T14:48:00.000Z';

        $this->mockFileGetContent->shouldReceive('get_contents')->once()->andReturn('<svg></svg>');

        $this->mockOmisePaymentOffline->shouldReceive('init_settings');
        $this->mockOmisePaymentOffline->shouldReceive('get_option');
        $this->mockOmisePaymentOffline->shouldReceive('load_order')->andReturn(true);
        $this->mockOmisePaymentOffline->shouldReceive('get_charge_id_from_order')->andReturn('charge_xxx');
        $this->mockOmisePaymentOffline->shouldReceive('get_pending_status')->andReturn('pending');
        $this->mockOmisePaymentOffline->shouldReceive('file_get_contents')->andReturn('');

        $this->mockWcDateTime->shouldReceive('set_utc_offset');
        $this->mockWcDateTime->shouldReceive('format')->with('c')->andReturn($expiresAt);

        $this->mockOmiseCharge->shouldReceive('retrieve')->andReturn([
            'status' => 'pending',
            'expires_at' => $expiresAt,
            'source' => [
                'scannable_code' => [
                    'image' => [
                        'id' => 1,
                        'download_uri' => '',
                    ]
                ]
            ]
        ]);

        // check that qr_expires_at is passed to `omise-promptpay-count-down` script with omise object
        $this->mockLocalizeScript->shouldReceive('call')
            ->with('omise-promptpay-count-down', 'omise', [
                'qr_expires_at' => $expiresAt
            ]);

        $GLOBALS['mock_wp_localize_script'] = $this->mockLocalizeScript;

        function wp_localize_script($scriptName, $object, $params) {
            return $GLOBALS['mock_wp_localize_script']->call($scriptName, $object, $params);
        }

        $obj = new Omise_Payment_Promptpay();
        $result = $obj->display_qrcode($this->mockOrder, 'view');
        $this->assertNull($result);
    }
}
