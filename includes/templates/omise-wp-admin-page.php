<?php defined( 'ABSPATH' ) or die ( "No direct script access allowed." ); ?>

<div class='wrap'>
	<h1>Omise Dashboard</h1>

	<?php
	if ( isset( $viewData['balance'] ) ) :
		$balance       = $viewData["balance"];
		$redirect_back = urlencode( remove_query_arg( 'omise_result_msg', $_SERVER['REQUEST_URI'] ) );

		$omise = array(
			'account' => array(
				'email' => 'nam@omise.co',
			),
			'balance' => array(
				'livemode'  => $balance->livemode,
				'currency'  => $balance->currency,
				'total'     => $balance->total,
				'available' => $balance->available,
			),
			'transactions' => array()
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

		<h2>Request a transfer</h2>
		<div class="omise-transfer-form">
			<form id='omise_create_transfer_form' action='<?php echo admin_url( 'admin-post.php' ); ?>' method='POST'>
				<input type="hidden" name="action" value="omise_create_transfer" />
				<?php wp_nonce_field( 'omise_create_transfer', 'omise_create_transfer_nonce', FALSE ); ?>
				<input type="hidden" name="_wp_http_referer" value="<?php echo $redirect_back; ?>"> <input type="checkbox" name="full_transfer" id="omise_transfer_full_amount" value="full_amount" />
				<label for="omise_transfer_full_amount">Full available amount (<?php echo $balance->formatted_available ?>)</label>
				<br />
				<div id="omise_transfer_specific_amount">
					<div>------ Or ------</div>
					Partial amount : <input type='text' name='omise_transfer_amount' id='omise_transfer_amount' required /> THB
				</div>
				<br /> <input type='submit' value='submit' class='button button-primary' />
			</form>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $viewData['message'] ) ) : ?>
		<div class="omise-dashboard-white-box"><?php echo esc_html( $viewData["message"] ) ?></div>
	<?php endif; ?>
</div>