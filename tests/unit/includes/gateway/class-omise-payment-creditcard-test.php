<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Omise_Payment_CreditCard_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $omisePaymentMock = Mockery::mock('overload:Omise_Payment');
        $omisePaymentMock->shouldReceive('init_settings');
        $omisePaymentMock->shouldReceive('get_option');
        $omisePaymentMock->shouldReceive('is_test')
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

        require_once __DIR__ . '/../../../../includes/gateway/traits/charge-request-builder-trait.php';
        require_once __DIR__ . '/../../../../includes/gateway/abstract-omise-payment-base-card.php';
        require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment-creditcard.php';
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function testClassIsInitializedProperly()
    {
        Monkey\Functions\stubs( [
            'wp_kses' => null,
		] );
        Monkey\Functions\expect('wp_enqueue_script');
        Monkey\Functions\expect('plugins_url');
        $creditCard = new Omise_Payment_Creditcard;
        
        $this->assertEquals($creditCard->source_type, 'credit_card');
        $this->assertEquals(
            $creditCard->method_description,
            'Accept payment through <strong>Credit / Debit Card</strong> via Opn Payments.'
        );

        $this->assertEquals(
            $creditCard->form_fields['accept_amex']['description'],
            'This only controls the icons displayed on the checkout page.<br />It is not related to card processing on Opn Payments.'
        );
    }
}
