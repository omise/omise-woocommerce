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
								<?php foreach ( $backend->available_plans as $installment_plan ) : ?>
									<option value="<?php echo $installment_plan['term_length']; ?>">
										<?php
										echo sprintf(
											__( '%d months', 'omise', 'omise_installment_term_option' ),
											$installment_plan['term_length']
										);
										?>

										<?php
										echo sprintf(
											__( '( %s / months )', 'omise', 'omise_installment_payment_per_month' ),
											wc_price( $installment_plan['monthly_amount'] )
										);
										?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php if ( ! $viewData['is_zero_interest'] ): ?>
								<br/><span class="omise-installment-interest-rate">
									<?php echo sprintf( __( '( interest %g%% )', 'omise' ), $backend->interest_rate ); ?>
								</span>
							<?php endif; ?>
						</div>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="omise-buttom-note">
			<p>
				<?php echo $viewData['is_zero_interest'] ? __( 'All installment payments are interest free', 'omise' ) : __( 'Monthly payment rates shown may be inaccurate as interest rates are subject to change by its bank issuer.', 'omise' ); ?>
			</p>
		</div>
	</fieldset>
<?php else: ?>
	<p>
		<?php echo __( 'There are no installment plans available for this purchase amount  (minimum amount is 2,000 THB).', 'omise' ); ?>
	</p>
<?php endif; ?>
