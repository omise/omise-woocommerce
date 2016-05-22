<?php defined( 'ABSPATH' ) or die ( "No direct script access allowed." ); ?>

<div class='wrap'>
	<h1>Omise Dashboard</h1>

	<?php Omise_Util::render_partial( 'message-box', array( 'message' => $viewData['message'], 'message_type' => $viewData['message_type'] ) ); ?>

	<?php
	if ( isset( $viewData['balance'] ) ) :
		$balance       = $viewData["balance"];
		$charges       = $viewData["charges"];
		$redirect_back = urlencode( remove_query_arg( 'omise_result_msg', $_SERVER['REQUEST_URI'] ) );

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

		<h1>Transactions History</h1>

		<h2 class="nav-tab-wrapper wp-clearfix">
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'omise-plugin-admin-page' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab<?php if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'locations' != $_GET['action'] ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Charges' ); ?></a>
		</h2>

		<!-- Charge list -->
		<div id="Omise-ChargeList">
			<form id="Omise-ChargeFilters" method="GET">
				<input type="hidden" name="page" value="Omise-dashboard" />
				<?php
				$charge_table = new Omise_Charges_Table( $charges );
				$charge_table->prepare_items();
				$charge_table->display();
				?>
			</form>
		</div>
	<?php endif; ?>

	<?php Omise_Util::render_partial( 'transfer-box', $omise ); ?>
</div>