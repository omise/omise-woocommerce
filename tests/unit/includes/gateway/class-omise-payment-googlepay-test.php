<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Payment_GooglePay_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        Monkey\Functions\expect('wp_kses')->andReturn(null);
        Monkey\Functions\expect('wp_enqueue_script');
        Monkey\Functions\expect('plugins_url');
        Monkey\Functions\expect('get_woocommerce_currency')->andReturn('thb');

        // dummy version
        if (!defined('WC_VERSION')) {
            define('WC_VERSION', '1.0.0');
        }
        
        $omisePaymentMock = Mockery::mock('overload:Omise_Payment');
        $omisePaymentMock->shouldReceive('init_settings');
        $omisePaymentMock->shouldReceive('get_option');
        $omisePaymentMock->shouldReceive('is_test')
            ->andReturn(true);
        $omisePaymentMock->shouldReceive('public_key')
            ->andReturn('pkey_123');

        $omiseCardImage = Mockery::mock('alias:Omise_Card_Image');
        $omiseCardImage->shouldReceive('get_css')->times(4);
        $omiseCardImage->shouldReceive('get_visa_image')->once();
        $omiseCardImage->shouldReceive('get_visa_default_display')->once();
        $omiseCardImage->shouldReceive('get_mastercard_image')->once();
        $omiseCardImage->shouldReceive('get_mastercard_default_display')->once();
        $omiseCardImage->shouldReceive('get_jcb_image')->once();
        $omiseCardImage->shouldReceive('get_jcb_default_display')->once();
        $omiseCardImage->shouldReceive('get_amex_image')->once();
        $omiseCardImage->shouldReceive('get_amex_default_display')->once();

        require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-base-card.php';
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-googlepay.php';
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function init_googlepay_config()
    {
        $expected = [
            'environment' => 'TEST',
            'api_version' => 2,
            'api_version_minor' => 0,
            'allowed_auth_methods' => ['PAN_ONLY'],
            'allowed_card_networks' => [],
            'billing_address_required' => false,
            'phone_number_required' => false,
            'public_key' => 'pkey_123',
            'merchant_id' => null,
            'price_status' => 'NOT_CURRENTLY_KNOWN',
            'currency' => 'thb',
        ];

        $googlepay = new Omise_Payment_GooglePay;

        $reflection = new \ReflectionClass($googlepay);
        $config = $reflection->getMethod('init_googlepay_config');
        $config->setAccessible(true);
        $config->invoke($googlepay);

        $result = $googlepay->googlepay_config;

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function google_pay_button_scripts()
    {
        $googlepay = new Omise_Payment_GooglePay;

        $reflection = new \ReflectionClass($googlepay);
        $config = $reflection->getMethod('google_pay_button_scripts');
        $config->setAccessible(true);
        $result = $config->invoke($googlepay);

        $expected = [
            'script' => "<script type='module'>
                const button = document.createElement('google-pay-button')
                button.setAttribute('environment', 'TEST')
                button.setAttribute('button-type', 'pay')
                button.setAttribute('button-color', 'black')
                button.paymentRequest = {
                    apiVersion: 2,
                    apiVersionMinor: 0,
                    allowedPaymentMethods: [
                        {
                            type: 'CARD',
                            parameters: {
                                allowedAuthMethods: [\"PAN_ONLY\"],
                                allowedCardNetworks: [],
                                billingAddressRequired: false,
                                billingAddressParameters: {
                                    format: 'FULL',
                                    phoneNumberRequired: false,
                                },
                            },
                            tokenizationSpecification: {
                                type: 'PAYMENT_GATEWAY',
                                parameters: {
                                    gateway: 'omise',
                                    gatewayMerchantId: 'pkey_123',
                                },
                            },
                        },
                    ],
                    merchantInfo: {
                        merchantId: '',
                    },
                    transactionInfo: {
                        totalPriceStatus: NOT_CURRENTLY_KNOWN,
                        currencyCode: 'thb',
                    },
                }

                const div = document.getElementById('googlepay-button-container')
                div.appendChild(button)

                function toggleOrderButton() {
                    const placeOrderButton = document.getElementById('place_order')
                    const paymentBox = document.getElementById('payment_method_omise_googlepay')

                    if (document.getElementsByClassName('omise-secondary-text googlepay-selected').length < 1) {
                        placeOrderButton.style.display = paymentBox.checked ? 'none' : 'inline-block'
                    }
                }

                toggleOrderButton()
                const paymentMethods = document.getElementsByClassName('input-radio')
                Array.from(paymentMethods).forEach((el) => {
                    el.addEventListener('click', toggleOrderButton)
                })
            </script>"
        ];

        $this->assertEquals($expected, $result);
    }
}