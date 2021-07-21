<fieldset id="omise-form-truemoney">
	<?php _e( 'TrueMoney phone number', 'omise' ); ?><br/>

	<p id="omise_phone_number_default_field" class="form-row form-row-wide omise-label-inline">
		<input id="omise_phone_number_default" type="checkbox" name="omise_phone_number_default" value="1" checked="checked" />
		<label for="omise_phone_number_default"><?php _e( 'Same as Billing Detail', 'omise' ); ?></label>
	</p>

	<p id="omise_phone_number_field" class="form-row form-row-wide" style="display: none;">
		<span class="woocommerce-input-wrapper">
			<input id="omise_phone_number" class="input-text" name="omise_phone_number" type="tel" autocomplete="off">
		</span>
	</p>

	<p class="omise-secondary-text">
		<?php _e( 'One-Time Password (OTP) will be sent to the phone number above', 'omise' ); ?>
	</p>
</fieldset>

<script type="text/javascript">
	var phone_number_field   = document.getElementById( 'omise_phone_number_field' );
	var phone_number_default = document.getElementById( 'omise_phone_number_default' );

	phone_number_default.addEventListener( 'change', ( e ) => {
		phone_number_field.style.display = e.target.checked ? "none" : "block";
	} );
</script>
