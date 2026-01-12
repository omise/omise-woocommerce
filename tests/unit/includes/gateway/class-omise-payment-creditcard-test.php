<?php

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 */
class Omise_Payment_CreditCard_Test extends Omise_Test_Case
{
    protected $omisePaymentMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->omisePaymentMock = Mockery::mock('overload:Omise_Payment');
        $this->omisePaymentMock->shouldReceive('init_settings');
        $this->omisePaymentMock->shouldReceive('get_option')
            ->andReturn(true);
        $this->omisePaymentMock->shouldReceive('is_test')
            ->andReturn(true);

        $wc = Mockery::mock( 'WC' );
        $wc->shouldReceive( 'plugin_url' )->andReturn( '' );
        Monkey\Functions\expect( 'WC' )->andReturn( $wc );

        Monkey\Functions\expect('add_action')->andReturn(null);
        Monkey\Functions\expect('wp_kses')
            ->times(3)
            ->andReturn(
                'Accept payment through <strong>Credit / Debit Card</strong> via Opn Payments.',
                'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Opn Payments.'
            );

        require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-base-card.php';
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-creditcard.php';
    }

    public function test_class_is_initialized_properly()
    {
        $creditCard = new Omise_Payment_Creditcard;

        $this->assertEquals('card', $creditCard->source_type);
        $this->assertEquals(
            'Accept payment through <strong>Credit / Debit Card</strong> via Opn Payments.',
            $creditCard->method_description,
        );

        $this->assertEquals(
            'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Opn Payments.',
            $creditCard->form_fields['accept_amex']['description'],
        );
    }

    public function test_get_secure_form_config() {
        $cardFormCustomization = Mockery::mock('alias:Omise_Page_Card_From_Customization');
        $cardFormCustomization->shouldReceive('get_instance')->andReturn($cardFormCustomization);
        $cardFormCustomization->shouldReceive('get_design_setting')->andReturn(['abc' => ['xyz']]);

        $creditCard = new Omise_Payment_Creditcard;

        $config = $creditCard->get_secure_form_config();

        $this->assertArrayHasKey('card_form_theme', $config);
        $this->assertArrayHasKey('card_icons', $config);
        $this->assertArrayHasKey('form_design', $config);
    }

    public function test_get_existing_cards_for_user_logged_in() {
        Monkey\Functions\expect('is_user_logged_in')->andReturn(true);
        Monkey\Functions\expect('wp_get_current_user')->andReturn((object)['test_omise_customer_id' => 1]);

        $omise = Mockery::mock('alias:Omise');
        $omise->shouldReceive('settings')->andReturn($this->mockOmiseSetting('pkey_test_123', 'skey_test_123'));
        Monkey\Functions\expect('Omise')->andReturn($omise);

        $customer = Mockery::mock('stdClass');
        $customer->shouldReceive('cards')->andReturn(['data' => [
            [
                'object' => 'card',
                'id' => 'card_test_123',
                'brand' => 'Visa',
                'last_digits' => '4242',
                'expiration_month' => 2,
                'expiration_year' => 2024,
            ]
        ]]);
        $omiseCustomer = Mockery::mock('alias:OmiseCustomer');
        $omiseCustomer->shouldReceive('retrieve')->andReturn($customer);

        $creditCard = new Omise_Payment_Creditcard;
        $data = $creditCard->get_existing_cards();

        $this->assertIsArray($data);
        $this->assertEquals(true, $data['user_logged_in']);
        $this->assertArrayHasKey('existing_cards', $data);
    }

    public function test_get_existing_cards_for_user_not_logged_in() {
        Monkey\Functions\expect('is_user_logged_in')->andReturn(false);

        $creditCard = new Omise_Payment_Creditcard;
        $data = $creditCard->get_existing_cards();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('user_logged_in', $data);
    }

    public function test_form_fields() {
        $credit_card = new Omise_Payment_Creditcard;

        $this->assertEquals([
            'enabled',
            'title',
            'description',
            'advanced',
            'payment_action',
            'card_form_theme',
            'accept_visa',
            'accept_mastercard',
            'accept_jcb',
            'accept_diners',
            'accept_discover',
            'accept_amex',
        ], array_keys($credit_card->form_fields));
    }
}
