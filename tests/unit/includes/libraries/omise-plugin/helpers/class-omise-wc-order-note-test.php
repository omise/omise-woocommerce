<?php

use Brain\Monkey;

require_once __DIR__ . '/../../../../class-omise-unit-test.php';
require_once __DIR__ . '/../../../gateway/bootstrap-test-setup.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Omise_WC_Order_Note_Test extends Omise_Test_Case
{
  private $projectRoot = __DIR__ . '/../../../../../..';

  public function setUp(): void
  {
    parent::setUp();

    Monkey\Functions\expect('add_action')->andReturn(null);
    Monkey\Functions\expect('do_action')->andReturn(null);
    Monkey\Functions\stubs([
      'wp_kses' => null,
      'esc_url' => null,
    ]);

    require_once $this->projectRoot . '/includes/libraries/omise-plugin/helpers/class-omise-wc-order-note.php';
    require_once $this->projectRoot . '/includes/classes/class-omise-charge.php';
    require_once $this->projectRoot . '/omise-woocommerce.php';
    require_once $this->projectRoot . '/includes/class-omise-localization.php';
  }

  public function test_get_charge_created_note()
  {
    $charge = [
      'id' => 'chrg_638p9dqptlmwrrtdp8n',
      'missing_3ds_fields' => [],
    ];

    $note = Omise_WC_Order_Note::get_charge_created_note($charge);

    $this->assertEquals($note, 'Omise: Charge (ID: chrg_638p9dqptlmwrrtdp8n) has been created');
  }

  public function test_get_charge_created_note_with_missing_3ds_fields()
  {
    $charge = [
      'id' => 'chrg_638p9dqptlmwrrtdp8n',
      'missing_3ds_fields' => ['email', 'phone_number'],
    ];

    $note = Omise_WC_Order_Note::get_charge_created_note($charge);

    $this->assertEquals($note, 'Omise: Charge (ID: chrg_638p9dqptlmwrrtdp8n) has been created<br/><b>Missing 3DS Fields:</b> email, phone_number');
  }

  public function test_get_payment_failed_note_with_string_message()
  {
    $note = Omise_WC_Order_Note::get_payment_failed_note(null, 'Something went wrong');

    $this->assertEquals($note, 'Omise: Payment failed.<br/><b>Error Description:</b> Something went wrong');
  }

  public function test_get_payment_failed_note_with_charge()
  {
    $charge = [
      'failure_code' => 'insufficient_fund',
      'failure_message' => 'insufficient funds in the account or the card has reached the credit limit'
    ];

    $note = Omise_WC_Order_Note::get_payment_failed_note($charge);

    $this->assertEquals('Omise: Payment failed.<br/><b>Error Description:</b> (insufficient_fund) insufficient funds in the account or the card has reached the credit limit', $note);
  }

  public function test_get_payment_failed_note_with_charge_merchant_advice()
  {
    $charge = [
      'failure_code' => 'insufficient_fund',
      'failure_message' => 'insufficient funds in the account or the card has reached the credit limit',
      'merchant_code' => '9003',
      'merchant_advice' => 'Do not retry the transaction with the same card',
    ];

    $note = Omise_WC_Order_Note::get_payment_failed_note($charge);

    $this->assertEquals('Omise: Payment failed.<br/><b>Error Description:</b> (insufficient_fund) insufficient funds in the account or the card has reached the credit limit<br/><b>Advice:</b> Do not retry the transaction with the same card', $note);
  }

  public function test_get_processing_authorized_uri_note_for_3ds_transaction()
  {
    $authorize_uri = 'https://3dsms.staging-omise.co/payments/pay2_656ej5e4knqicc36w56/authorize';
    $charge = [
      'status' => 'pending',
      'authorized' => false,
      'authorize_uri' => $authorize_uri,
    ];

    $note = Omise_WC_Order_Note::get_processing_authorized_uri_note($charge);

    $this->assertEquals("Omise: Processing a 3-D Secure payment, redirecting buyer to {$authorize_uri}", $note);
  }

  public function test_get_processing_authorized_uri_note_for_passkey_transaction()
  {
    $authorize_uri = 'https://3dsms.staging-omise.co/payments/pay2_656eks0m6jc1g2vuqgc/authorize?signature=nOXpqgb0&sigv=1&token=5438637f';
    $charge = [
      'status' => 'pending',
      'authorized' => false,
      'authenticated_by' => 'PASSKEY',
      'authorize_uri' => $authorize_uri,
    ];

    $note = Omise_WC_Order_Note::get_processing_authorized_uri_note($charge);

    $this->assertEquals("Omise: Processing a Passkey payment, redirecting buyer to {$authorize_uri}", $note);
  }
}
