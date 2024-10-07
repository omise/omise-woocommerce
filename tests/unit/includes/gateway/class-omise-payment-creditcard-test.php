<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Payment_CreditCard_Test extends TestCase
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

        $omiseCardImage = Mockery::mock('alias:Omise_Card_Image');
        $omiseCardImage->shouldReceive('get_css')->times(6);
        $omiseCardImage->shouldReceive('get_visa_image')->once();
        $omiseCardImage->shouldReceive('get_visa_default_display')->once();
        $omiseCardImage->shouldReceive('get_mastercard_image')->once();
        $omiseCardImage->shouldReceive('get_mastercard_default_display')->once();
        $omiseCardImage->shouldReceive('get_jcb_image')->once();
        $omiseCardImage->shouldReceive('get_jcb_default_display')->once();
        $omiseCardImage->shouldReceive('get_diners_image')->once();
        $omiseCardImage->shouldReceive('get_diners_default_display')->once();
        $omiseCardImage->shouldReceive('get_amex_image')->once();
        $omiseCardImage->shouldReceive('get_amex_default_display')->once();
        $omiseCardImage->shouldReceive('get_discover_image')->once();
        $omiseCardImage->shouldReceive('get_discover_default_display')->once();

        // dummy version
        if (!defined('WC_VERSION')) {
            define('WC_VERSION', '1.0.0');
        }

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

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function test_class_is_initialized_properly()
    {
        $creditCard = new Omise_Payment_Creditcard;
        
        $this->assertEquals('credit_card', $creditCard->source_type);
        $this->assertEquals(
            'Accept payment through <strong>Credit / Debit Card</strong> via Opn Payments.',
            $creditCard->method_description,
        );

        $this->assertEquals(
            'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Opn Payments.',
            $creditCard->form_fields['accept_amex']['description'],
        );
    }

    /**
     * @test
     */
    public function get_secure_form_config() {
        $cardFormCustomization = Mockery::mock('alias:Omise_Page_Card_From_Customization');
        $cardFormCustomization->shouldReceive('get_instance')->andReturn($cardFormCustomization);
        $cardFormCustomization->shouldReceive('get_design_setting')->andReturn(['abc' => ['xyz']]);

        $creditCard = new Omise_Payment_Creditcard;

        $config = $creditCard->get_secure_form_config();

        $this->assertArrayHasKey('card_form_theme', $config);
        $this->assertArrayHasKey('card_icons', $config);
        $this->assertArrayHasKey('form_design', $config);
    }

    /**
     * @test
     */
    public function get_existing_cards_for_user_logged_in() {
        Monkey\Functions\expect('is_user_logged_in')->andReturn(true);
        Monkey\Functions\expect('wp_get_current_user')->andReturn((object)['test_omise_customer_id' => 1]);

        $customerCard = Mockery::mock('alias:OmiseCustomerCard');
        $customerCard->shouldReceive('get')->andReturn(1);

        $creditCard = new Omise_Payment_Creditcard;

        $data = $creditCard->get_existing_cards();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('user_logged_in', $data);
    }

    public function get_existing_cards_for_user_not_logged_in() {
        Monkey\Functions\expect('is_user_logged_in')->andReturn(false);

        $creditCard = new Omise_Payment_Creditcard;
        $data = $creditCard->get_existing_cards();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('user_logged_in', $data);
    }
}
