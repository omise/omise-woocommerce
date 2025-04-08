<?php

require_once __DIR__ . '/../../class-omise-unit-test.php';
require_once __DIR__ . '/bootstrap-test-setup.php';

use Brain\Monkey;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_Payment_Test extends Bootstrap_Test_Setup
{
  protected $omisePayment;
  protected $mockOmiseSetting;
  protected $mockOrderNoteHelper;

  protected function setUp(): void
  {
    Monkey\Functions\expect('add_action');
    Monkey\Functions\expect('do_action');
    Monkey\Functions\expect('add_filter');
    Monkey\Functions\stubs(
      [
        'is_admin' => false,
        'is_checkout' => true,
        'is_wc_endpoint_url' => false,
        'wp_kses' => null,
      ]
    );
    $this->mockOmiseSetting = $this->mockOmiseSetting('pkey_xxx', 'skey_xxx');

    require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/obj/OmiseObject.php';
    require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/res/OmiseApiResource.php';
    require_once __DIR__ . '/../../../../includes/libraries/omise-php/lib/omise/OmiseCapability.php';
    require_once __DIR__ . '/../../../../includes/class-omise-capability.php';
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
        parent::__construct();
        $this->wcOrder = \Mockery::mock();
      }
      public function charge($_order_id, $_order)
      {
        // Do Nothing
      }
      public function result($_order_id, $_order, $_charge)
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

  /**
   * @dataProvider is_available_data_provider
   */
  public function test_is_available_returns_boolean_whether_method_is_supported_from_capability($sourceType, $expected)
  {
    WC_Payment_Gateway::$is_available = true;

    Mockery::mock('overload:' . OmiseHttpExecutor::class)
      ->shouldReceive('execute')
      ->once()
      ->andReturn(load_fixture('omise-capability-get'));

    $result = ($this->omisePaymentImpl($sourceType))->is_available();

    $this->assertEquals($expected, $result);
  }

  public function is_available_data_provider()
  {
    return [
      ['card', true],
      ['fpx', false],
    ];
  }

  public function test_is_available_returns_false_if_gateway_is_not_enabled()
  {
    WC_Payment_Gateway::$is_available = false;

    $result = ($this->omisePaymentImpl('card'))->is_available();

    $this->assertFalse($result);
  }

  public function test_is_available_returns_false_if_capability_returns_null()
  {
    WC_Payment_Gateway::$is_available = true;

    Monkey\Functions\stubs(
      [
        'is_admin' => false,
        'is_checkout' => false,
        'is_wc_endpoint_url' => false,
      ],
    );

    $result = ($this->omisePaymentImpl('card'))->is_available();

    $this->assertFalse($result);
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

  public function test_get_provider_returns_null_if_no_payment_method_enabled_in_settings()
  {
    $this->mockOmiseSetting->shouldReceive('get_settings')->andReturn([
      'backends' => null,
    ]);

    $provider = ($this->omisePaymentImpl('touch_n_go'))->get_provider();

    $this->assertNull($provider);
  }

  public function test_get_provider_returns_null_if_the_source_type_is_not_enabled_in_settings()
  {
    $this->mockOmiseSetting->shouldReceive('get_settings')->andReturn([
      'backends' => [
        (object) [
          'object' => 'payment_method',
          'name' => 'card',
        ]
      ],
    ]);

    $provider = ($this->omisePaymentImpl('touch_n_go'))->get_provider();

    $this->assertNull($provider);
  }

  public function test_get_provider_returns_the_payment_method_provider()
  {
    $this->mockOmiseSetting->shouldReceive('get_settings')->andReturn([
      'backends' => [
        (object) [
          'object' => 'payment_method',
          'name' => 'touch_n_go',
          'provider' => 'Alipay_plus'
        ]
      ],
    ]);

    $provider = ($this->omisePaymentImpl('touch_n_go'))->get_provider();

    $this->assertEquals('Alipay_plus', $provider);
  }

  private function mockOmiseSetting($pkey, $skey)
  {
    $omiseSettingMock = Mockery::mock('alias:' . Omise_Setting::class);

    $omiseSettingMock->shouldReceive('instance')->andReturn($omiseSettingMock);
    $omiseSettingMock->shouldReceive('public_key')->andReturn($pkey);
    $omiseSettingMock->shouldReceive('secret_key')->andReturn($skey);
    $omiseSettingMock->shouldReceive('get_settings')->andReturn([])->byDefault();

    return $omiseSettingMock;
  }

  private function omisePaymentImpl($sourceType)
  {
    return new class ($sourceType) extends Omise_Payment {
      public function __construct($sourceType)
      {
        parent::__construct();
        $this->source_type = $sourceType;
      }
      public function charge($_order_id, $_order)
      {
        // Do Nothing
      }
      public function result($_order_id, $_order, $_charge)
      {
        // Do Nothing
      }
    };
  }
}
