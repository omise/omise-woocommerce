<?php $omise = $partial_data; ?>

<!-- Transfer box -->
<div id="Omise-TransferBoxWrapper">
	<div id="Omise-TransferBox">
		<form id="Omise-TransferForm" accept-charset="UTF-8" action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">

			<input type="hidden" name="action" value="omise_create_transfer" />
			<input type="hidden" name="_wp_http_referer" value="<?php echo urlencode( remove_query_arg( 'omise_result_msg', $_SERVER['REQUEST_URI'] ) ); ?>">
			<?php wp_nonce_field( 'omise_create_transfer', 'omise_create_transfer_nonce', FALSE ); ?>

			<div class="Omise-Element RadioBox SELECTED">
				<input checked="checked" id="transfer_type_full" name="transfer[type]" type="radio" value="full_amount">
				<h3 class="Omise-Element RadioBoxLabel">Full transfer</h3>
				<p class="Omise-Element RadioBoxDescription">
					Transfer the whole available balance (<?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['available'] ); ?>) to your account
				</p>
			</div>

			<div class="Omise-Element RadioBox">
				<input id="transfer_type_partial" name="transfer[type]" type="radio" value="partial">
				<h3 class="Omise-Element RadioBoxLabel">Partial transfer</h3>
				<p class="Omise-Element RadioBoxDescription">
					Transfer only part of the available balance (<?php echo OmisePluginHelperCurrency::format( $omise['balance']['currency'], $omise['balance']['available'] ); ?>) to your account
				</p>
				<div class="Omise-Element Field">
					<label class="Omise-Element FieldLabel" for="omise_transfer_amount">Amount</label>
					<input class="Omise-Element TextField" id="omise_transfer_amount" name="omise_transfer_amount" type="text" placeholder="0">
				</div>
			</div>

			<div class="Omise-Element FooterAction">
				<input id="Omise-TransferCreateAction" class="Omise-Element FooterActionElement button button-primary" data-confirm="Do you really want to create this transfer?" name="commit" type="submit" value="Transfer">
				<span id="Omise-TransferCancelAction" class="Omise-Element FooterActionElement button action">Cancel</span>
				<span class="Omise-Element FooterActionElement Spinner"></span>
			</div>

			<footer class="Omise-Element SeparatedFootnote">
				Transfers are processed daily in chronological order. If balance is insufficient, transfers are retried on the next day.
			</footer>
		</form>
	</div>
</div>