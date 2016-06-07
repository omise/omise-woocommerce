<h1>Omise Dashboard</h1>

<?php Omise_Util::render_partial( 'message-box', array( 'message' => $viewData['message'], 'message_type' => $viewData['message_type'] ) ); ?>

<?php
if ( isset( $viewData['balance'] ) ) :
	$balance = $viewData["balance"];

	$omise = array(
		'account' => array(
			'email' => $viewData["email"],
		),
		'balance' => array(
			'livemode'  => $balance->livemode,
			'currency'  => $balance->currency,
			'total'     => $balance->total,
			'available' => $balance->available,
		)
	);
?>

	<!-- Account Info -->
	<div class="Omise-Box Omise-Account">
		<dl>
			<!-- Account email -->
			<dt>Account: </dt>
			<dd><?php echo $omise['account']['email']; ?></dd>

			<!-- Account status -->
			<dt>Mode: </dt>
			<dd><strong><?php echo $omise['balance']['livemode'] ? '<span class="Omise-LIVEMODE">LIVE</span>' : '<span class="Omise-TESTMODE">TEST</span>'; ?></strong></dd>

			<!-- Current Currency -->
			<dt>Currency: </dt>
			<dd><?php echo strtoupper( $omise['balance']['currency'] ); ?></dd>

			<!-- Payment Action -->
			<dt>Auto Capture: </dt>
			<dd><?php echo $viewData['auto_capture']; ?></dd>

			<!-- 3D Secure enabled? -->
			<dt>3D-Secure: </dt>
			<dd><?php echo $viewData['support_3dsecure']; ?></dd>
		</dl>
	</div>

	<!-- Balance -->
	<div class="Omise-Balance Omise-Clearfix">
		<div class="left"><span class="Omise-BalanceAmount"><?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['total'] ); ?></span><br/>Total Balance</div>
		<div class="right"><span class="Omise-BalanceAmount"><?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['available'] ); ?></span><br/>Transferable Balance</div>
	</div>

	<div>
		<span id="Omise-BalanceTransferTab" class="Omise-BalanceTransferTab">Setup a transfer</span>
	</div>

	<?php Omise_Util::render_partial( 'transfer-box', $omise ); ?>
<?php endif; ?>