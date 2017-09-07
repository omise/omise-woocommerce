<div class="wrap omise">
	<h1><?php echo $title; ?></h1>
	<h2><?php echo _e( 'Payment Settings', 'omise' ); ?></h2>
	<p>
		<?php
		echo sprintf(
			wp_kses(
				__( 'All of your keys can be found at your Omise dashboard, check the following links.<br/><a href="%s">Test keys</a> or <a href="%s">Live keys</a> (login required)', 'omise' ),
				array(
					'br' => array(),
					'a'  => array( 'href' => array() )
				)
			),
			esc_url( 'https://dashboard.omise.co/test/keys' ),
			esc_url( 'https://dashboard.omise.co/live/keys' )
		);
		?>
	</p>
	<form method="POST">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="sandbox"><?php echo __( 'Test mode', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<label for="sandbox">
								<input name="sandbox" type="checkbox" id="sandbox" value="1" <?php echo 'yes' === $settings['payment']['sandbox'] ? 'checked="checked"' : ''; ?>>
								<?php echo __( 'Enabling test mode means that all your transactions will be performed under the Omise test account.', 'omise' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="test_public_key"><?php echo __( 'Public key for test', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="test_public_key" type="text" id="test_public_key" value="<?php echo $settings['payment']['test_public_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="test_private_key"><?php echo __( 'Secret key for test', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="test_private_key" type="text" id="test_private_key" value="<?php echo $settings['payment']['test_private_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="live_public_key"><?php echo __( 'Public key for live', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="live_public_key" type="text" id="live_public_key" value="<?php echo $settings['payment']['live_public_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="live_private_key"><?php echo __( 'Secret key for live', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="live_private_key" type="password" id="live_private_key" value="<?php echo $settings['payment']['live_private_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Save Settings', 'omise' ) ); ?>
	</form>
</div>
