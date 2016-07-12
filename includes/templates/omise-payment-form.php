<div id="omise_cc_form">
<?php 
	$showExistingCards = $viewData["user_logged_in"] && isset($viewData["existingCards"]->data) && sizeof($viewData["existingCards"]->data) > 0;
	if ( $showExistingCards ) {
?>
<p class="form-row form-row-wide">
	<?php echo Omise_Util::translate( 'Select card' ); ?> : <br/>
	
		<?php 
			foreach ( $viewData["existingCards"]->data as $card ) {
				echo "<input type='radio' name='card_id' value='{$card->id}' />" . Omise_Util::translate( 'Card ends with' ) . "{$card->last_digits}<br/>";
			}
		?>
</p>
&nbsp;<input type='radio' id='new_card_info' name='card_id' value='' /><?php echo Omise_Util::translate( 'New payment information' ); ?>
<?php } ?>
<fieldset id="new_card_form" class="<?php echo $showExistingCards ? 'omise-hidden':''; ?>">
	<?php 
		require_once('omise-cc-form.php'); 
		if ( $viewData["user_logged_in"] ) {
	?>
	<p class="form-row form-row-wide">
		<input type='checkbox' name='omise_save_customer_card' id='omise_save_customer_card' /> 
		<label for="omise_save_customer_card" class="inline"><?php echo Omise_Util::translate( 'Save card for next time' ); ?></label>
	</p>
	<?php 
		}
	?>
	<div class="clear"></div>
</fieldset>
</div>