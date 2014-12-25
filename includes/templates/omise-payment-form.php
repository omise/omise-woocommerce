<div id="omise_cc_form">
<?php wp_nonce_field('omise_checkout','omise_nonce'); ?>
<?php 
	if($viewData["user_logged_in"]){
?>
<p class="form-row form-row-wide">
	Select card : 
	<select name='card_id' id='card_id'>
		<option value=''>-- Please select --</option>
		<?php 
		if (isset($viewData["existingCards"]->data)){
			foreach($viewData["existingCards"]->data as $card){
				echo "<option value='{$card->id}'>Card ends with {$card->last_digits}</option>";
			}
		}
		?>
	</select>
	<br/>Or charge with a new card
</p>
<?php } ?>
	<fieldset>
		<?php 
			require_once('omise-cc-form.php'); 
			if($viewData["user_logged_in"]){
		?>
		<p class="form-row form-row-wide">
			<input type='checkbox' name='omise_save_customer_card' id='omise_save_customer_card' /> Save card for next time
		</p>
		<?php 
			}
		?>
		<div class="clear"></div>
	</fieldset>
</div>