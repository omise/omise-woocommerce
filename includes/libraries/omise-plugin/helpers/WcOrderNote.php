<?php
if (!class_exists('OmisePluginWcOrderNote')) {
  class OmisePluginHelperWcOrderNote
  {
    protected static $allowedHtml = [
      'br' => [],
      'b' => [],
    ];

    /**
     * @param OmiseCharge $charge
     */
    public static function getChargeCreatedNote($charge)
    {
      return self::sanitize(
        sprintf(
          __('Omise: Charge (ID: %s) has been created', 'omise'),
          $charge['id']
        ) . self::getMissing3dsFields($charge)
      );
    }

    /**
     * @param OmiseCharge|null $charge
     * @param string $reason
     */
    public static function getPaymentFailedNote($charge, $reason = '')
    {
      $reason = $charge ? Omise_Charge::get_error_message($charge) . self::getMerchantAdvice($charge) : $reason;
      $message = sprintf(__('Omise: Payment failed.<br/><b>Error Description:</b> %s', 'omise'), $reason);

      return self::sanitize($message);
    }

    private static function sanitize($message)
    {
      return wp_kses($message, self::$allowedHtml);
    }

    private static function getMerchantAdvice($charge)
    {
      if (empty($charge['merchant_advice'])) {
        return '';
      }

      return '<br/><b>Advice:</b> ' . $charge['merchant_advice'];
    }

    private static function getMissing3dsFields($charge)
    {
      if (empty($charge['missing_3ds_fields']) || !is_array($charge['missing_3ds_fields'])) {
        return '';
      }

      return '<br/><b>Missing 3DS Fields:</b> ' . join(', ', $charge['missing_3ds_fields']);
    }
  }
}
