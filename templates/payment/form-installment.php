<?php if ( ! empty( $viewData['installment_backends'] ) ) : ?>
	<fieldset id="omise-form-installment">
		<ul class="omise-banks-list">
			<?php foreach ( $viewData['installment_backends'] as $backend ) : ?>
				<li class="item">
					<input id="<?php echo $backend->_id; ?>" type="radio" name="source[type]" value="<?php echo $backend->_id; ?>" />
					<label for="<?php echo $backend->_id; ?>">
						<div class="bank-logo <?php echo $backend->provider_code; ?>"></div>
						<div class="bank-label">
							<span class="title"><?php echo $backend->provider_name; ?></span><br/>
							<select id="<?php echo $backend->_id; ?>_installment_terms" name="<?php echo $backend->_id; ?>_installment_terms" class="installment-term-select-box">
								<option>Select term</option>
								<?php foreach ( $backend->allowed_installment_terms as $installment_term ) : ?>
									<option value="<?php echo $installment_term['term']; ?>"><?php echo $installment_term['term']; ?> <?php echo __('months'); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
<?php else: ?>
	<p>
		<?php echo __( 'There are no installment plans available for this purchase amount  (minimum amount is 3,000 THB).', 'omise' ); ?>
	</p>
<?php endif; ?>
