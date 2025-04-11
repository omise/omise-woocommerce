<?php if ( ! empty( $viewData['mobile_banking_backends'] ) ) : ?>
	<fieldset id="omise-form-mobilebanking">
		<ul class="omise-banks-list">
			<?php foreach ( $viewData['mobile_banking_backends'] as $backend ) : ?>
				<li class="item mobile-banking">
					<div>
						<input id="<?php echo $backend->name; ?>" type="radio" name="omise-offsite" value="<?php echo $backend->name; ?>" />
						<label for="<?php echo $backend->name; ?>">
							<div class="mobile-banking-logo <?php echo $backend->provider_logo; ?>"></div>
							<div class="mobile-banking-label">
								<span class="title"><?php echo $backend->provider_name; ?></span><br/>
							</div>
						</label>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
<?php else: ?>
	<p>
		<?php echo __( 'There are no payment methods available.', 'omise' ); ?>
	</p>
<?php endif; ?>
