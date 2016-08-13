<?php $viewData = $partial_data; ?>

<h1><?php echo Omise_Util::translate( 'Omise Dashboard' ); ?></h1>

<?php Omise_Util::render_partial( 'message-box', array( 'message' => $viewData['message'], 'message_type' => $viewData['message_type'] ) ); ?>

<?php
if ( isset( $viewData['balance'] ) ) :
	$balance = $viewData["balance"];

	$omise = array(
		'account' => array(
			'email' => $viewData['email']
		),
		'balance' => array(
			'livemode'  => $balance->livemode,
			'currency'  => $balance->currency,
			'total'     => $balance->total,
			'available' => $balance->available
		)
	);
?>

	<!-- Account Info -->
	<div class="Omise-Box Omise-Account">
		<dl>
			<!-- Account email -->
			<dt><?php echo Omise_Util::translate( 'Account' ); ?>: </dt>
			<dd><?php echo $omise['account']['email']; ?></dd>

			<!-- Account status -->
			<dt><?php echo Omise_Util::translate( 'Mode' ); ?>: </dt>
			<dd><strong><?php echo $omise['balance']['livemode'] ? '<span class="Omise-LIVEMODE">' . Omise_Util::translate( 'LIVE' ) . '</span>' : '<span class="Omise-TESTMODE">' . Omise_Util::translate( 'TEST' ) . '</span>'; ?></strong></dd>

			<!-- Current Currency -->
			<dt><?php echo Omise_Util::translate( 'Currency' ); ?>: </dt>
			<dd><?php echo strtoupper( $omise['balance']['currency'] ); ?></dd>

			<!-- Payment Action -->
			<dt><?php echo Omise_Util::translate( 'Auto Capture', 'Account information' ); ?>: </dt>
			<dd><?php echo $viewData['auto_capture'] == 'YES' ? Omise_Util::translate( 'YES', 'Auto capture status is enabled' ) : Omise_Util::translate( 'NO', 'Auto capture status is disabled' ); ?></dd>

			<!-- 3D Secure enabled? -->
			<dt><?php echo Omise_Util::translate( '3-D Secure' ); ?>: </dt>
			<dd><?php echo $viewData['support_3dsecure'] == 'ENABLED' ? Omise_Util::translate( 'ENABLED', '3-D Secure status is enabled' ) : Omise_Util::translate( 'DISABLED', '3-D Secure status is disabled' ); ?></dd>
		</dl>
	</div>

	<!-- Balance -->
	<div class="Omise-Balance Omise-Clearfix">
		<div class="left"><span class="Omise-BalanceAmount"><?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['total'] ); ?></span><br/><?php echo Omise_Util::translate( 'Total Balance' ); ?></div>
		<div class="right"><span class="Omise-BalanceAmount"><?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['available'] ); ?></span><br/><?php echo Omise_Util::translate( 'Transferable Balance' ); ?></div>
	</div>

	<div>
		<span id="Omise-BalanceTransferTab" class="Omise-BalanceTransferTab"><?php echo Omise_Util::translate( 'Setup a transfer' ); ?></span>
	</div>

	<?php Omise_Util::render_partial( 'transfer-box', $omise ); ?>
<?php endif; ?>