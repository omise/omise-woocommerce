<?php

require_once __DIR__ . '/class-omise-offsite-test.php';

class Omise_Payment_Truemoney_Test extends Omise_Offsite_Test
{
    private $mockOmiseCapability;

    public function setUp(): void
    {
        $this->sourceType = 'truemoney';
        parent::setUp();
        Brain\Monkey\Functions\expect('is_admin')
			->with('123')
			->andReturn(true);
		Brain\Monkey\Functions\expect('is_checkout')
			->with('123')
			->andReturn(true);
		Brain\Monkey\Functions\expect('is_wc_endpoint_url')
			->with('123')
			->andReturn(true);
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-truemoney.php';
        $this->mockOmiseCapability = Mockery::mock('alias:Omise_Capabilities');
    }

    public function test_get_charge_request()
    {
        $this->mockOmiseCapability->shouldReceive('retrieve')->once();
        // set source type to truemoney wallet
        $obj = new Omise_Payment_Truemoney();
        $obj->source_type = 'truemoney';
        $orderId = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['omise_phone_number_default'] = true;
        $result = $obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($orderMock->get_billing_phone(), $result['source']['phone_number']);
    }

    public function test_get_charge_request_when_customer_overrides_default_phone()
    {
        $this->mockOmiseCapability->shouldReceive('retrieve')->once();
        $orderId = 'order_123';
        $expectedAmount = 999999;
        $expectedCurrency = 'thb';
        $orderMock = $this->getOrderMock($expectedAmount, $expectedCurrency);

        $_POST['omise_phone_number'] = '1234567890';

        $obj = new Omise_Payment_Truemoney();
        $result = $obj->get_charge_request($orderId, $orderMock);

        $this->assertEquals($this->sourceType, $result['source']['type']);
    }

    public function test_charge()
    {
        $this->mockOmiseCapability->shouldReceive('retrieve')->once();
        $_POST['omise_phone_number_default'] = true;
        $obj = new Omise_Payment_Truemoney();
        $this->getChargeTest($obj);
    }

    public function test_get_source_returns_jumpapp()
    {
        $this->mockOmiseCapability->shouldReceive('retrieve');
        $obj = new Omise_Payment_Truemoney();
        $source_type = $obj->get_source();
        $this->assertEquals('truemoney_jumpapp', $source_type);
    }

    public function test_get_source_returns_wallet()
    {
        $this->mockOmiseCapability->shouldReceive('retrieve')
            ->andReturn(new class() {
                public function get_truemoney_backend($source_type) {
                    if ('truemoney' === $source_type) {
                        return (object)[
                            'truemoney' => [
                                'type' => 'truemoney',
                                'currencies' => [
                                    'thb'
                                ],
                                'amount' => [
                                    'min' => 2000,
                                    'max' => 500000000000
                                ]
                            ]
                        ];
                    }

                    return null;
                }
            });

        $obj = new Omise_Payment_Truemoney();
        $source_type = $obj->get_source();
        $this->assertEquals('truemoney', $source_type);
    }

    public function test_get_source_returns_jumpapp_when_both_are_enabled()
    {
        $this->mockOmiseCapability->shouldReceive('retrieve')
            ->andReturn(new class() {
                public function get_truemoney_backend($source_type) {
                    if ('truemoney' === $source_type) {
                        return (object)[
                            'truemoney' => [
                                'type' => 'truemoney',
                                'currencies' => [
                                    'thb'
                                ],
                                'amount' => [
                                    'min' => 2000,
                                    'max' => 500000000000
                                ]
                            ]
                        ];
                    }

                    return (object)[
                        'truemoney_jumpapp' => [
                            'type' => 'truemoney_jumpapp',
                            'currencies' => [
                                'thb'
                            ],
                            'amount' => [
                                'min' => 2000,
                                'max' => 500000000000
                            ]
                        ]
                    ];
                }
            });

        $obj = new Omise_Payment_Truemoney();
        $source_type = $obj->get_source();
        $this->assertEquals('truemoney_jumpapp', $source_type);
    }
}
