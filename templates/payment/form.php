<div id="omise_cc_form">
	<?php $showExistingCards = $viewData['user_logged_in'] && isset( $viewData['existingCards']->data ) && sizeof( $viewData['existingCards']->data ) > 0; ?>

	<?php if ( $showExistingCards ) : ?>
		<p class="form-row form-row-wide">
			<?php echo __( 'Select card', 'omise' ); ?> : <br/>

				<?php foreach ( $viewData['existingCards']->data as $card ) : ?>
						<?php echo "<input type='radio' name='card_id' value='{$card->id}' />" . __( 'Card ends with', 'omise' ) . " {$card->last_digits}<br/>"; ?>
				<?php endforeach; ?>
		</p>
		&nbsp;<input type="radio" id="new_card_info" name="card_id" value="" /><?php echo __( 'New payment information', 'omise' ); ?>
	<?php endif; ?>

	<fieldset id="new_card_form" class="<?php echo $showExistingCards ? 'omise-hidden' : ''; ?>">

		<?php require_once('form-creditcard.php'); ?>

		<?php if ( $viewData['user_logged_in'] ) : ?>
			<p class="form-row form-row-wide">
				<input type="checkbox" name="omise_save_customer_card" id="omise_save_customer_card" />
				<label for="omise_save_customer_card" class="inline"><?php echo __( 'Save card for next time', 'omise' ); ?></label>
			</p>
		<?php endif; ?>

		<div class="clear"></div>
	</fieldset>
</div>
