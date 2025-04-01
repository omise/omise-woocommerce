<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

final class SyncOrderTraitTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();

    Monkey\setUp();
    Monkey\Functions\expect('add_action')->andReturn(null);
    Monkey\Functions\stubs([
      'wp_kses' => null,
    ]);

    require_once __DIR__ . '/../../../../../includes/gateway/traits/sync-order-trait.php';
    require_once __DIR__ . '/../../../../../includes/gateway/class-omise-payment.php';
    require_once __DIR__ . '/../../../../../includes/libraries/omise-plugin/helpers/WcOrderNote.php';
    require_once __DIR__ . '/../../../../../includes/classes/class-omise-charge.php';
    require_once __DIR__ . '/../../../../../includes/class-omise-localization.php';
    require_once __DIR__ . '/../../../../../omise-woocommerce.php';
  }

  protected function tearDown(): void
  {
    Brain\Monkey\tearDown();
    Mockery::close();
  }

  public function testSyncPaymentWithFailedCharge()
  {
    $mockOrder = Mockery::mock('WC_Order');
    $mockOrder->shouldReceive('get_transaction_id')->andReturn('chrg_638p9dqptlmwrrtdp8n');
    $mockOrder->shouldReceive('has_status')->with('failed')->andReturn(false);

    $omiseCharge = Mockery::mock('alias:' . OmiseCharge::class);
    $omiseCharge->shouldReceive('retrieve')->andReturn([
      'id' => 'chrg_638p9dqptlmwrrtdp8n',
      'status' => 'failed',
      'failure_code' => 'invalid_charge',
      'failure_message' => 'invalid charge',
    ]);

    $syncOrderTrait = Mockery::mock(Sync_Order::class);
    $syncOrderTrait->shouldReceive('load_order')->andReturn($mockOrder);
    $syncOrderTrait->shouldReceive('get_charge_id_from_order')->andReturn('chrg_638p9dqptlmwrrtdp8n');
    $syncOrderTrait->shouldReceive('set_order_transaction_id')->with('chrg_638p9dqptlmwrrtdp8n');
    $syncOrderTrait->shouldReceive('order')->andReturn($mockOrder);

    // Expectations for failed charge
    $syncOrderTrait->shouldReceive('delete_capture_metadata')->once();
    $mockOrder->shouldReceive('add_order_note')->with('Omise: Payment failed.<br/>(invalid_charge) invalid charge (manual sync)')->once();
    $mockOrder->shouldReceive('update_status')->with('failed')->once();

    $syncOrderTrait->sync_payment($mockOrder);

    $this->assertTrue(true);
  }
}
