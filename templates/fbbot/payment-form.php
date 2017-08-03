<div>
	<form id="omise_cc_messenger_form" action="<?php echo get_site_url(); ?>/wp-json/omisemsgbot/v1/callback_fbbot_checkout" method="POST">
		<?php
			$product = Omise_FBBot_WCProduct::create( $product_id );
		?>
		<div id="notification_content">
			<ul></ul>
		</div>
	
		<p class="form-row" >
			<label for="product_name">Product : <strong><?php echo $product->name; ?></strong></label>
			<label for="product_price">Total : <strong><?php echo $product->price.' '.$product->currency; ?></strong></label>
		</p>

		<p class="form-row form-row-wide omise-required-field">
			<label for="omise_card_name"><?php echo _x( 'Name on card', 'Card holder name at payment form', 'omise' ); ?></label>
			<input id="omise_card_name" class="input-text" type="text"
				maxlength="255" autocomplete="off" placeholder="<?php echo _x( 'FULL NAME', 'Placeholder for card holder name', 'omise' ); ?>"
				name="omise_card_name">
		</p>

		<p class="form-row form-row-wide omise-required-field">
			<label for="customer_email"><?php echo _x( 'Email address', 'Contact email at payment form', 'omise' ); ?></label>
			<input id="customer_email" class="input-text" type="text"
				maxlength="255" autocomplete="off" placeholder="<?php echo _x( 'your@email.com', 'Contact email at payment form', 'omise' ); ?>"
				name="customer_email">
		</p>

		<p class="form-row form-row-wide omise-required-field">
			<label for="omise_card_number"><?php echo _x( 'Card number', 'Card number at payment form', 'omise' ); ?></label>
			<input id="omise_card_number" class="input-text" type="text"
				maxlength="20" autocomplete="off" placeholder="<?php echo _x( '•••• •••• •••• ••••', 'Placeholder for card number', 'omise' ); ?>"
				name="omise_card_number">
		</p>

		<p class="form-row form-row-first omise-required-field">
			<label for="omise_card_expiration_month"><?php echo _x( 'Expiration month', 'Expiration month at payment form', 'omise' ); ?></label>
			<input id="omise_card_expiration_month" class="input-text" type="text"
				autocomplete="off" placeholder="<?php echo _x( 'MM', 'Placeholder for expiration month', 'omise' ); ?>" name="omise_card_expiration_month">
		</p>

		<p class="form-row form-row-last omise-required-field">
			<label for="omise_card_expiration_year"><?php echo _x( 'Expiration year', 'Expiration year at payment form', 'omise' ); ?></label>
			<input id="omise_card_expiration_year" class="input-text" type="text"
				autocomplete="off" placeholder="<?php echo _x( 'YYYY', 'Placeholder for expiration year', 'omise' ); ?>"
				name="omise_card_expiration_year">
		</p>

		<p class="form-row form-row-first omise-required-field">
			<label for="omise_card_security_code"><?php echo _x( 'Security code', 'Security Code at payment form', 'omise' ); ?></label>
			<input id="omise_card_security_code"
				class="input-text" type="password" autocomplete="off"
				placeholder="<?php echo _x( '•••', 'Placeholder for security Code', 'omise' ); ?>" name="omise_card_security_code">
		</p>

		<p class="form-row form-row-first">
			<input type="submit" value="Pay : <?php echo $product->price.' '.$product->currency; ?>">
			<input type="hidden" name="messenger_id" value=<?php echo $messenger_id; ?>></input>
			<input type="hidden" name="product_id" value=<?php echo $product_id; ?>></input>
			<input type="hidden" name="omise_token"></input>
		</p>

	</form>
</div>
