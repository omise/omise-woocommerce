<?php
defined ( 'ABSPATH' ) or die ( "No direct script access allowed." );
?>
<div class="wrap">
  <div class="omise-dashboard-header">
    <h2>Omise Merchant Account</h2>
  </div>
  <div class="omise-dashboard-account-mode" style="color: <?php echo $viewData["current_account_mode"]=='TEST' ? 'orange' : 'green'; ?>;">
    <strong><?php echo $viewData["current_account_mode"]; ?> MODE</strong>
  </div>
  <br class="omise-clear-both" />
  <div>
    Omise payment gateway plugin is in <strong><?php echo $viewData["current_account_mode"]; ?></strong>
    mode. Go to WooCommerce settings to change mode.
  </div>
<?php
if (isset ( $viewData ["balance"] )) {
  $balance = $viewData ["balance"];
  $redirect_back = urlencode ( remove_query_arg ( 'omise_result_msg', $_SERVER ['REQUEST_URI'] ) );
  ?>
<br />
  <div class="omise-dashboard-balance">
    <div class="omise-dashboard-balance-label">Total Balance :</div>
    <div class="omise-dashboard-balance-value"><?php echo $balance->formatted_total ?></div>
  </div>
  <div class="omise-dashboard-balance">
    <div class="omise-dashboard-balance-label">Available Balance :</div>
    <div class="omise-dashboard-balance-value"><?php echo $balance->formatted_available ?></div>
  </div>
  <br class="omise-clear-both" />
  <h2>Request a transfer</h2>
  <div class="omise-transfer-form">
    <form id='omise_create_transfer_form'
      action='<?php echo admin_url( 'admin-post.php' ); ?>'
      method='POST'>
      <input type="hidden" name="action" value="omise_create_transfer" />
    <?php wp_nonce_field( 'omise_create_transfer', 'omise_create_transfer_nonce', FALSE ); ?>
    <input type="hidden" name="_wp_http_referer"
        value="<?php echo $redirect_back; ?>"> <input type="checkbox"
        name="full_transfer" id="omise_transfer_full_amount"
        value="full_amount" /> <label for="omise_transfer_full_amount">Full available amount (<?php echo $balance->formatted_available ?>)</label>
      <br />
      <div id="omise_transfer_specific_amount">
        <div>------ Or ------</div>
        Partial amount : <input type='text' name='omise_transfer_amount'
          id='omise_transfer_amount' required /> THB
      </div>
      <br /> <input type='submit' value='submit'
        class='button button-primary' />
    </form>
  </div>

<?php
}

if (! empty ( $viewData ["message"] )) {
  ?>
    <div class="omise-dashboard-white-box"><?php echo esc_html($viewData["message"]) ?></div>
  <?php
}
?>

</div>