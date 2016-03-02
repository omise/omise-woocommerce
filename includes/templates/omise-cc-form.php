<div class="form-row form-row-first omise-required-field">
    <label for="omise_card_name">Card Holder Name <span class="required">*</span></label>
    <input id="omise_card_name" class="input-text" type="text"
        maxlength="255" autocomplete="off" placeholder="Card Holder Name"
        name="omise_card_name">
</div>
<div class="form-row form-row-first omise-required-field omise-clear-both">
    <label for="omise_card_number">Card Number <span class="required">*</span></label>
    <input id="omise_card_number" class="input-text" type="text"
        maxlength="20" autocomplete="off" placeholder="Card Number"
        name="omise_card_number">
</div>
<div class="form-row form-row-wide omise-required-field">
  <div class="omise-form-inline">
    <label for="omise_card_expiration_month">Expiration Month <span
        class="required">*</span></label>
    <select id="omise_card_expiration_month" name="omise_card_expiration_month">
        <option value="01">01</option>
        <option value="02">02</option>
        <option value="03">03</option>
        <option value="04">04</option>
        <option value="05">05</option>
        <option value="06">06</option>
        <option value="07">07</option>
        <option value="08">08</option>
        <option value="09">09</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
    </select>
  </div>
  <div class="omise-form-inline">
    <label for="omise_card_expiration_year">Expiration Year <span
        class="required">*</span></label>
    <select id="omise_card_expiration_year" name="omise_card_expiration_year">
    <?php
        $this_year = (int) date( "Y" );
        for ( $year = $this_year; $year <= $this_year + 10; $year++ ) {
    ?>
            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
    <?php
        }
    ?>
    </select>
  </div>
</div>
<div class="form-row form-row-first omise-required-field">
    <label for="omise_card_security_code">Security Code <span
        class="required">*</span></label>
    <input id="omise_card_security_code"
        class="input-text" type="password" autocomplete="off"
        placeholder="CVC" name="omise_card_security_code">
</div>
