<?php 
$showExistingCards = $viewData['user_logged_in'] && isset($viewData['existingCards']['data']) && sizeof($viewData['existingCards']['data']) > 0;
$hideRememberCard = $viewData['user_logged_in'] ? 'no' : 'yes';
?>

<div id="omise_cc_form">
	<?php if ($showExistingCards) : ?>
		<h3><?php _e('Use an existing card', 'omise'); ?></h3>
		<ul class="omise-customer-card-list">
			<?php foreach ($viewData['existingCards']['data'] as $row => $card) : ?>
				<li class="item">
					<input <?php echo $row === 0 ? 'checked=checked' : ''; ?> id="card-<?php echo $card['id']; ?>" type="radio" name="card_id" value="<?php echo $card['id']; ?>" />
					<label for="card-<?php echo $card['id']; ?>">
						<?php echo '<strong>' . $card['brand'] . '</strong> xxxx' . $card['last_digits']; ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<div>
		<?php if ($showExistingCards) : ?>
			<input id="new_card_info" type="radio" name="card_id" value="" />
			<label id="label-new_card_info" for="new_card_info">
				<h3><?php _e('Create a charge using new card', 'omise'); ?></h3>
			</label>
		<?php endif; ?>

		<div class="omise-new-card-form <?php echo $showExistingCards ? 'card-exists' : ''; ?>">
			<div id="omise-card" style="width:100%; max-width: 400px;"></div>
			<input type="hidden" name="omise_save_customer_card" class="omise_save_customer_card" />
			<div class="clear"></div>
		</div>
	</div>
</div>

<script>
	window.CARD_FORM_THEME = "<?php echo $viewData['card_form_theme'] ?>";
	window.CARD_BRAND_ICONS = JSON.parse(`<?php echo json_encode($viewData['card_icons']) ?>`);
	window.FORM_DESIGN = JSON.parse(`<?php echo json_encode($viewData['form_design']) ?>`);
	window.LOCALE = `<?php echo get_locale(); ?>`;
	window.HIDE_REMEMBER_CARD = `<?php echo $hideRememberCard ?>` == 'yes' ? true : false;
</script>
