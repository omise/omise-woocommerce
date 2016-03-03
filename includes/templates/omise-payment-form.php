<div id="omise_cc_form">
<?php
    $showExistingCards = $viewData["user_logged_in"] && isset( $viewData["existingCards"]->data ) && sizeof( $viewData["existingCards"]->data ) > 0;
    if ( true == $showExistingCards ) {
?>
    <p class="form-row form-row-wide">
        Select card: <br/>
        <?php
            $is_first = true;
            foreach ( $viewData["existingCards"]->data as $card ) {
        ?>
                <input type="radio" name="card_id" value="<?php echo $card->id; ?>" <?php if ( true == $is_first ) { echo "checked"; $is_first = false; } ?> />Card ends with <?php echo $card->last_digits; ?><br/>
        <?php
            }
        ?>
    </p>
&nbsp;<input type="radio" id="new_card_info" name="card_id" value="" />New payment information
<?php } ?>
<fieldset id="new_card_form" class="<?php echo $showExistingCards ? 'omise-hidden':''; ?>">
    <?php
        require_once( "omise-cc-form.php" );
        if ( $viewData["user_logged_in"] ) {
    ?>
            <p class="form-row form-row-wide">
                <input type="checkbox" name="omise_save_customer_card" id="omise_save_customer_card" />
                <label for="omise_save_customer_card" class="inline">Save card for next time</label>
            </p>
    <?php
        }
    ?>
    <div class="clear"></div>
</fieldset>
</div>
