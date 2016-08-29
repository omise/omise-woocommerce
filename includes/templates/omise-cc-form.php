<p class="form-row form-row-wide omise-required-field">
	<label for="omise_card_name"><?php echo Omise_Util::translate( 'Name', 'Card holder name at payment form' ); ?> <span class="required">*</span></label>
	<input id="omise_card_name" class="input-text" type="text"
		maxlength="255" autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'Name', 'Placeholder for card holder name' ); ?>"
		name="omise_card_name">
</p>
<p class="form-row form-row-wide omise-required-field">
	<label for="omise_card_number"><?php echo Omise_Util::translate( 'Card Number', 'Card number at payment form'); ?> <span class="required">*</span></label>
	<input id="omise_card_number" class="input-text" type="text"
		maxlength="20" autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'Card Number', 'Placeholder for card number'); ?>"
		name="omise_card_number">
</p>
<p class="form-row form-row-first omise-required-field">
	<label for="omise_card_expiration_month"><?php echo Omise_Util::translate( 'Expiration Month', 'Expiration month at payment form' ); ?> <span class="required">*</span></label>
	<input id="omise_card_expiration_month" class="input-text" type="text"
		autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'MM', 'Placeholder for expiration month' ); ?>" name="omise_card_expiration_month">
</p>
<p class="form-row form-row-last omise-required-field">
	<label for="omise_card_expiration_year"><?php echo Omise_Util::translate( 'Expiration Year', 'Expiration year at payment form' ); ?> <span class="required">*</span></label>
	<input id="omise_card_expiration_year" class="input-text" type="text"
		autocomplete="off" placeholder="<?php echo Omise_Util::translate( 'YYYY', 'Placeholder for expiration year' ); ?>"
		name="omise_card_expiration_year">
</p>
<p class="form-row form-row-first omise-required-field">
	<label for="omise_card_security_code"><?php echo Omise_Util::translate( 'Security Code', 'Security Code at payment form'); ?> <span class="required">*</span></label>
	<input id="omise_card_security_code"
		class="input-text" type="password" autocomplete="off"
		placeholder="<?php echo Omise_Util::translate( 'Security Code', 'Placeholder for security Code'); ?>" name="omise_card_security_code">
</p>