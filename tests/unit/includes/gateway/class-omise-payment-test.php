<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/bootstrap-test-setup.php';

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

abstract class WC_Payment_Gateway
{
}

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Test extends Bootstrap_Test_Setup
{
  protected $omisePayment;
  protected $mockOrderNoteHelper;

  protected function setUp(): void
  {
    Monkey\Functions\expect('add_action');
    Monkey\Functions\expect('do_action');
    Monkey\Functions\stubs(
      [
        'wp_kses' => null,
      ]
    );
    $this->mockOmiseSetting('pkey_xxx', 'skey_xxx');


    require_once __DIR__ . '/../../../../includes/gateway/traits/sync-order-trait.php';
    require_once __DIR__ . '/../../../../includes/gateway/class-omise-payment.php';
    require_once __DIR__ . '/../../../../includes/class-omise-localization.php';
    require_once __DIR__ . '/../../../../includes/classes/class-omise-charge.php';
    require_once __DIR__ . '/../../../../omise-woocommerce.php';

    $this->mockOrderNoteHelper = \Mockery::mock('alias:' . OmisePluginHelperWcOrderNote::class);
    $this->omisePayment = new class extends Omise_Payment {
      public $wcOrder = null;

      public function __construct()
      {
        $this->wcOrder = \Mockery::mock();
      }
      public function charge($order_id, $order)
      {
        // Do Nothing
      }
      public function result($order_id, $order, $charge)
      {
        // Do Nothing
      }
      public function test_payment_failed($charge, $reason = '')
      {
        $this->payment_failed($charge, $reason);
      }

      public function order()
      {
        return $this->wcOrder;
      }
    };
  }

  public function test_payment_failed_updates_the_order_and_throws_exception()
  {
    $orderNote = 'My note';

    $this->mockOrderNoteHelper->shouldReceive('getPaymentFailedNote')->once()->with(null, 'Something went wrong')->andReturn($orderNote);
    $this->omisePayment->wcOrder->shouldReceive('add_order_note')->once()->with($orderNote);
    $this->omisePayment->wcOrder->shouldReceive('update_status')->once()->with('failed');
    $this->expectException(Exception::class);

    $this->omisePayment->test_payment_failed(null, 'Something went wrong');
  }

  public function test_payment_failed_throws_exception_with_reason()
  {
    $this->mockOrderNoteHelper->shouldReceive('getPaymentFailedNote')->once();
    $this->omisePayment->wcOrder->shouldReceive('add_order_note')->once();
    $this->omisePayment->wcOrder->shouldReceive('update_status')->once();

    $this->expectExceptionMessage("It seems we've been unable to process your payment properly:<br/>Cannot process the payment");

    $this->omisePayment->test_payment_failed(null, 'Cannot process the payment');
  }

  public function test_payment_failed_throws_exception_with_charge_failure_message()
  {
    $this->mockOrderNoteHelper->shouldReceive('getPaymentFailedNote')->once();
    $this->omisePayment->wcOrder->shouldReceive('add_order_note')->once();
    $this->omisePayment->wcOrder->shouldReceive('update_status')->once();

    $this->expectExceptionMessage("It seems we've been unable to process your payment properly:<br/>(insufficient_fund) insufficient funds in the account or the card has reached the credit limit");

    $charge = [
      'failure_code' => 'insufficient_fund',
      'failure_message' => 'insufficient funds in the account or the card has reached the credit limit'
    ];

    $this->omisePayment->test_payment_failed($charge);
  }

  private function mockOmiseSetting($pkey, $skey)
  {
    $omiseSettingMock = Mockery::mock('alias:' . Omise_Setting::class);

    $omiseSettingMock->shouldReceive('instance')->andReturn($omiseSettingMock);
    $omiseSettingMock->shouldReceive('get_settings')->andReturn([]);

    return $omiseSettingMock;
  }
}
