<p class="form-row form-row-wide omise-required-field">
	<label for="omise_card_number"><?php _e( 'Card number', 'omise' ); ?></label>
	<input id="omise_card_number" class="input-text" type="text"
		maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••">
</p>

<p class="form-row form-row-wide omise-required-field">
	<label for="omise_card_name"><?php _e( 'Name on card', 'omise' ); ?></label>
	<input id="omise_card_name" class="input-text" type="text"
		maxlength="255" autocomplete="off" placeholder="<?php _e( 'FULL NAME', 'omise' ); ?>">
</p>
<p class="form-row form-row-first omise-required-field">
	<label for="omise_card_expiration_month"><?php _e( 'Expiration month', 'omise' ); ?></label>
	<input id="omise_card_expiration_month" class="input-text" type="text"
		autocomplete="off" placeholder="<?php _e( 'MM', 'omise' ); ?>">
</p>
<p class="form-row form-row-last omise-required-field">
	<label for="omise_card_expiration_year"><?php _e( 'Expiration year', 'omise' ); ?></label>
	<input id="omise_card_expiration_year" class="input-text" type="text"
		autocomplete="off" placeholder="<?php _e( 'YYYY', 'omise' ); ?>">
</p>
<p class="form-row form-row-first omise-required-field">
	<label for="omise_card_security_code"><?php _e( 'Security code', 'omise' ); ?></label>
	<input id="omise_card_security_code"
		class="input-text" type="password" autocomplete="off"
		placeholder="•••">
</p>
<script type="text/javascript">

	/**
	 * This reformats the input in #omise_card_number field
	 * e.g. 1234123412 to 1234 1234 12
	 * @param string value
	 */
	function format_cardnumber(value) {
		var card_number = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '')
		var matches = card_number.match(/\d{4,16}/g);
		var match = matches && matches[0] || ''
		var parts = []
		for (i=0, len=match.length; i<len; i+=4) {
			parts.push(match.substring(i, i+4))
		}
		if (parts.length) {
			return parts.join(' ')
		} else {
			return value
		}
	}

	document.getElementById('omise_card_number').oninput = function() {
		this.value = format_cardnumber(this.value)
	}
</script>

<script type="text/javascript">

  function GetCardType(number)
  {
      // visa
      var re = new RegExp("^4");
      if (number.match(re) != null)
          return "Visa";

      // Mastercard 
      // Updated for Mastercard 2017 BINs expansion
       if (/^(5[1-5][0-9]{14}|2(22[1-9][0-9]{12}|2[3-9][0-9]{13}|[3-6][0-9]{14}|7[0-1][0-9]{13}|720[0-9]{12}))$/.test(number)) 
          return "Mastercard";

      // AMEX
      re = new RegExp("^3[47]");
      if (number.match(re) != null)
          return "AMEX";

      // Discover
      re = new RegExp("^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)");
      if (number.match(re) != null)
          return "Discover";

      // Diners
      re = new RegExp("^36");
      if (number.match(re) != null)
          return "Diners";

      // Diners - Carte Blanche
      re = new RegExp("^30[0-5]");
      if (number.match(re) != null)
          return "Diners - Carte Blanche";

      // JCB
      re = new RegExp("^35(2[89]|[3-8][0-9])");
      if (number.match(re) != null)
          return "JCB";

      // Visa Electron
      re = new RegExp("^(4026|417500|4508|4844|491(3|7))");
      if (number.match(re) != null)
          return "Visa Electron";

      return "";
  }
  
  document.getElementById('omise_card_number').oninput = function() {
    this.value = GetCardType(this.value)
  }
</script>
