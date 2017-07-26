<p class="form-row form-row-wide omise-required-field">
	<label for="omise_card_number"><?php _e( 'Card number', 'omise' ); ?></label>
	<input id="omise_card_number" class="input-text" type="text"
		maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••"
		name="omise_card_number">
</p>

<p class="form-row form-row-wide omise-required-field">
	<label for="omise_card_name"><?php _e( 'Name on card', 'omise' ); ?></label>
	<input id="omise_card_name" class="input-text" type="text"
		maxlength="255" autocomplete="off" placeholder="<?php _e( 'FULL NAME', 'omise' ); ?>"
		name="omise_card_name">
</p>
<p class="form-row form-row-first omise-required-field">
	<label for="omise_card_expiration_month"><?php _e( 'Expiration month', 'omise' ); ?></label>
	<input id="omise_card_expiration_month" class="input-text" type="text"
		autocomplete="off" placeholder="<?php _e( 'MM', 'omise' ); ?>" name="omise_card_expiration_month">
</p>
<p class="form-row form-row-last omise-required-field">
	<label for="omise_card_expiration_year"><?php _e( 'Expiration year', 'omise' ); ?></label>
	<input id="omise_card_expiration_year" class="input-text" type="text"
		autocomplete="off" placeholder="<?php _e( 'YYYY', 'omise' ); ?>"
		name="omise_card_expiration_year">
</p>
<p class="form-row form-row-first omise-required-field">
	<label for="omise_card_security_code"><?php _e( 'Security code', 'omise' ); ?></label>
	<input id="omise_card_security_code"
		class="input-text" type="password" autocomplete="off"
		placeholder="•••" name="omise_card_security_code">
</p>
