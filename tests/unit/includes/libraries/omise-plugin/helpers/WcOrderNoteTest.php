<?php

use Brain\Monkey;

require_once __DIR__ . '/../../../../class-omise-unit-test.php';
require_once __DIR__ . '/../../../gateway/bootstrap-test-setup.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class OmisePluginHelperWcOrderNoteTest extends Bootstrap_Test_Setup
{
  private $projectRoot = __DIR__ . '/../../../../../..';

  public function setUp(): void
  {
    parent::setUp();

    Monkey\Functions\expect('add_action')->andReturn(null);
    Monkey\Functions\expect('do_action')->andReturn(null);
    Monkey\Functions\stubs([
      'wp_kses' => null,
    ]);

    require_once $this->projectRoot . '/includes/libraries/omise-plugin/helpers/WcOrderNote.php';
    require_once $this->projectRoot . '/includes/classes/class-omise-charge.php';
    require_once $this->projectRoot . '/omise-woocommerce.php';
    require_once $this->projectRoot . '/includes/class-omise-localization.php';

  }

  public function testGetChargeCreatedNote()
  {
    $charge = [
      'id' => 'chrg_638p9dqptlmwrrtdp8n',
      'missing_3ds_fields' => [],
    ];

    $note = OmisePluginHelperWcOrderNote::getChargeCreatedNote($charge);

    $this->assertEquals($note, 'Omise: Charge (ID: chrg_638p9dqptlmwrrtdp8n) has been created');
  }

  public function testGetChargeCreatedNoteWithMissing3DSFields()
  {
    $charge = [
      'id' => 'chrg_638p9dqptlmwrrtdp8n',
      'missing_3ds_fields' => ['email', 'phone_number'],
    ];

    $note = OmisePluginHelperWcOrderNote::getChargeCreatedNote($charge);

    $this->assertEquals($note, 'Omise: Charge (ID: chrg_638p9dqptlmwrrtdp8n) has been created<br/><b>Missing 3DS Fields:</b> email, phone_number');
  }

  public function testGetPaymentFailedNoteWithStringMessage()
  {
    $note = OmisePluginHelperWcOrderNote::getPaymentFailedNote(null, 'Something went wrong');

    $this->assertEquals($note, 'Omise: Payment failed.<br/><b>Error Description:</b> Something went wrong');
  }

  public function testGetPaymentFailedNoteWithCharge()
  {
    $charge = [
      'failure_code' => 'insufficient_fund',
      'failure_message' => 'insufficient funds in the account or the card has reached the credit limit'
    ];

    $note = OmisePluginHelperWcOrderNote::getPaymentFailedNote($charge);

    $this->assertEquals('Omise: Payment failed.<br/><b>Error Description:</b> (insufficient_fund) insufficient funds in the account or the card has reached the credit limit', $note);
  }

  public function testGetPaymentFailedNoteWithChargeMerchantAdvice()
  {
    $charge = [
      'failure_code' => 'insufficient_fund',
      'failure_message' => 'insufficient funds in the account or the card has reached the credit limit',
      'merchant_code' => '9003',
      'merchant_advice' => 'Do not retry the transaction with the same card',
    ];

    $note = OmisePluginHelperWcOrderNote::getPaymentFailedNote($charge);

    $this->assertEquals('Omise: Payment failed.<br/><b>Error Description:</b> (insufficient_fund) insufficient funds in the account or the card has reached the credit limit<br/><b>Advice:</b> Do not retry the transaction with the same card', $note);
  }
}
