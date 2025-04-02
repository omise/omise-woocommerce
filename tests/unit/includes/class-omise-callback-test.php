<?php

use Brain\Monkey;

require_once __DIR__ . '/../class-omise-unit-test.php';
require_once __DIR__ . '/gateway/bootstrap-test-setup.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Callback_Test extends Bootstrap_Test_Setup
{
  protected $mockOrderNoteHelper;

  public function setUp(): void
  {
    Monkey\Functions\expect('add_action');
    Monkey\Functions\expect('do_action');

    require_once __DIR__ . '/../../../includes/libraries/omise-plugin/helpers/request.php';
    require_once __DIR__ . '/../../..//includes/libraries/omise-plugin/helpers/WcOrderNote.php';
    require_once __DIR__ . '/../../../includes/class-omise-callback.php';
    require_once __DIR__ . '/../../../includes/class-omise-localization.php';
    require_once __DIR__ . '/../../../omise-woocommerce.php';

    // $this->mockOrderNoteHelper = \Mockery::mock('alias:' . OmisePluginHelperWcOrderNote::class);
  }

  public function testExecutePaymentFailedOrderUpdatesOrderAndShowsNotice()
  {
    Monkey\Functions\stubs([
      'sanitize_text_field' => null,
      'wp_kses' => null,
      'wc_get_checkout_url' => 'https://www.omise.co/redirect',
    ]);

    $mockOrder = Mockery::mock('WC_Abstract_Order');
    $mockOrder->shouldReceive('get_transaction_id')->andReturn('chrg_638p9dqptlmwrrtdp8n');
    $mockOrder->shouldReceive('has_status')->with('failed')->andReturn(false);
    $mockOrder->shouldReceive('get_meta')->andReturn('token_12345');

    $charge = [
      'source' => [
        "type" => "promptpay",
      ],
      'status' => 'failed',
      'failure_code' => 'internal_error',
      'failure_message' => 'request could not be completed due to an internal error',
    ];

    Mockery::mock('overload:OmiseCharge')
      ->shouldReceive('retrieve')
      ->with('chrg_638p9dqptlmwrrtdp8n')
      ->andReturn($charge);

    $_GET = [
      'order_id' => '100',
      'token' => 'token_12345',
    ];

    Monkey\Functions\expect('wc_get_order')->with('100')->once()->andReturn($mockOrder);

    // Expectation for handling failed order
    $mockOrder
      ->shouldReceive('add_order_note')
      ->once()
      ->with('OMISE: Validating the payment result...');
    $mockOrder
      ->shouldReceive('add_order_note')
      ->once()
      ->with('Omise: Payment failed.<br/><b>Error Description:</b> request could not be completed due to an internal error (code: internal_error)');

    $mockOrder->shouldReceive('update_status')->once()->with('failed');
    $mockOrder->shouldReceive('update_meta_data')->once()->with('is_omise_payment_resolved', 'yes');
    $mockOrder->shouldReceive('save')->once();
    Monkey\Functions\expect('wc_add_notice')
      ->with('request could not be completed due to an internal error (code: internal_error)')
      ->once();
    Monkey\Functions\expect('wp_redirect')
      ->with('https://www.omise.co/redirect')
      ->once()
      ->andThrow(new Exception('I\'m redirected'));

    $this->expectException(Exception::class);

    Omise_Callback::execute();
  }
}
