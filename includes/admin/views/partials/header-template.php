<?php $viewData = $partial_data; ?>

<h1><?php echo __( 'Omise Dashboard', 'omise' ); ?></h1>

<?php Omise_Util::render_partial( 'message-box', array( 'message' => $viewData['message'], 'message_type' => $viewData['message_type'] ) ); ?>

<?php
if ( isset( $viewData['balance'] ) ) :
	$balance = $viewData["balance"];

	$omise = array(
		'account' => array(
			'email' => $viewData['email']
		),
		'balance' => array(
			'livemode'  => $balance['livemode'],
			'currency'  => $balance['currency'],
			'total'     => $balance['total'],
			'available' => $balance['available']
		)
	);
?>

	<!-- Account Info -->
	<div class="Omise-Box Omise-Account">
		<dl>
			<!-- Account email -->
			<dt><?php echo __( 'Account', 'omise' ); ?>: </dt>
			<dd><?php echo $omise['account']['email']; ?></dd>

			<!-- Account status -->
			<dt><?php echo __( 'Mode', 'omise' ); ?>: </dt>
			<dd><strong><?php echo $omise['balance']['livemode'] ? '<span class="Omise-LIVEMODE">' . __( 'LIVE', 'omise' ) . '</span>' : '<span class="Omise-TESTMODE">' . __( 'TEST', 'omise' ) . '</span>'; ?></strong></dd>

			<!-- Current Currency -->
			<dt><?php echo __( 'Currency', 'omise' ); ?>: </dt>
			<dd><?php echo strtoupper( $omise['balance']['currency'] ); ?></dd>

			<!-- Payment Action -->
			<dt><?php echo _x( 'Auto Capture', 'Account information', 'omise' ); ?>: </dt>
			<dd><?php echo $viewData['auto_capture'] == 'YES' ? _x( 'YES', 'Auto capture status is enabled', 'omise' ) : _x( 'NO', 'Auto capture status is disabled', 'omise' ); ?></dd>

			<!-- 3D Secure enabled? -->
			<dt><?php echo __( '3-D Secure', 'omise' ); ?>: </dt>
			<dd><?php echo $viewData['support_3dsecure'] == 'ENABLED' ? _x( 'ENABLED', '3-D Secure status is enabled', 'omise' ) : _x( 'DISABLED', '3-D Secure status is disabled', 'omise' ); ?></dd>
		</dl>
	</div>

	<!-- Balance -->
	<div class="Omise-Balance Omise-Clearfix">
		<div class="left"><span class="Omise-BalanceAmount"><?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['total'] ); ?></span><br/><?php echo __( 'Total Balance', 'omise' ); ?></div>
		<div class="right"><span class="Omise-BalanceAmount"><?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['available'] ); ?></span><br/><?php echo __( 'Transferable Balance', 'omise' ); ?></div>
	</div>

	<div>
		<span id="Omise-BalanceTransferTab" class="Omise-BalanceTransferTab"><?php echo __( 'Setup a transfer', 'omise' ); ?></span>
	</div>

	<?php Omise_Util::render_partial( 'transfer-box', $omise ); ?>
<?php endif; ?>