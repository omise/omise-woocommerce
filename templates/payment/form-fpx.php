<?php if (!empty($viewData['fpx_banklist'])) : ?>
	<fieldset id="omise-form-installment">
		<ul class="omise-banks-list">
			<?php foreach ($viewData['fpx_banklist'] as $bank) : ?>
				<li class="fpx item">
					<?php if ($bank['active']) : ?>
						<input id="<?php echo $bank["code"]; ?>" type="radio" name="source[bank]" value="<?php echo $bank["code"]; ?>" />
					<?php endif; ?>
					<label for="<?php echo $bank["code"]; ?>" <?php if (!$bank['active']) {echo "class='offline'";} ?>>
						<div class="fpx-bank-logo <?php echo $bank["code"]; ?>"></div>
						<div class="fpx-bank-label">
							<span class="title"><?php echo $bank["name"]; ?></span>
						</div>
						<div class="omise-offline">
							<?php if (!$bank['active']) : ?>
								<p>Offline</p>
							<?php endif; ?>
						</div>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
<?php else : ?>
	<p>
		<?php echo __('FPX is currently not available.', 'omise'); ?>
	</p>
<?php endif; ?>