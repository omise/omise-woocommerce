<?php
if (! class_exists('OmisePluginWcOrderNote')) {
  class OmisePluginHelperWcOrderNote
  {
    protected static $allowed_html = [
      'br' => [],
      'b' => [],
    ];

    public static function getPaymentFailedNote($charge_or_msg) {
      $reason = is_string($charge_or_msg) ? $charge_or_msg : self::getFailureReason($charge_or_msg);
      $message = sprintf(__( 'Omise: Payment failed.<br/>%s', 'omise' ), $reason);

      return wp_kses($message, self::$allowed_html);
    }

    private static function getFailureReason($charge) {
      $detail = '';
      if (!empty($charge['merchant_advice'])) {
        $detail = '<br/><b>Advice:</b> '. $charge['merchant_advice'];
      }

      return Omise_Charge::get_error_message($charge) . $detail;
    }
  }
}
