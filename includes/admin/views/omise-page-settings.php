<div class="wrap omise">
	<style>
		.omise-notice-testmode {
			background: #ffce00;
			color: #575D66;
			border: 1px solid #efc200;
			border-left-width: 4px;
		}
	</style>

	<h1><?= $title; ?></h1>

	<?php $page->display_messages(); ?>

	<?php if ( 'yes' === $settings['sandbox'] ) : ?>
		<div class="notice omise-notice-testmode">
			<p><?php echo _e( 'You are in test mode. No actual payment is made in this mode', 'omise' ); ?></p>
		</div>
	<?php endif; ?>

	<h2><?php echo _e( 'Payment Settings', 'omise' ); ?></h2>

	<p>
		<?php
		echo sprintf(
			wp_kses(
				__( 'All of your keys can be found at your Opn Payments dashboard, check <a target="_blank" href="%s">here link</a> for the keys. (login required)', 'omise' ),
				array(
					'a'  => array( 'href' => array(), 'target' => array() )
				)
			),
			esc_url( 'https://dashboard.omise.co/v2/settings/keys' )
		);
		?>
	</p>

	<form method="POST">
		<!-- Section: account information -->
		<?php if ( $settings['account_email'] ) : ?>
			<hr />
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label><?php _e( 'Account status', 'omise' ); ?></label></th>
						<td>
							<fieldset>
								Connected: <em><?php echo $settings['account_email']; ?> (<?php echo $settings['account_country']; ?>)</em>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<hr />
		<?php endif; ?>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="sandbox"><?php _e( 'Test mode', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<label for="sandbox">
								<input name="sandbox" type="checkbox" id="sandbox" value="1" <?php echo 'yes' === $settings['sandbox'] ? 'checked="checked"' : ''; ?>>
								<?php _e( 'Enabling test mode means that all your transactions will be performed under the Opn Payments test account.', 'omise' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="test_public_key"><?php _e( 'Public key for test', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="test_public_key" type="text" id="test_public_key" value="<?php echo $settings['test_public_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="test_private_key"><?php _e( 'Secret key for test', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="test_private_key" type="text" id="test_private_key" value="<?php echo $settings['test_private_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="live_public_key"><?php _e( 'Public key for live', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="live_public_key" type="text" id="live_public_key" value="<?php echo $settings['live_public_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="live_private_key"><?php _e( 'Secret key for live', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<input name="live_private_key" type="password" id="live_private_key" value="<?php echo $settings['live_private_key']; ?>" class="regular-text" />
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<hr />

		<table class="form-table">
			<tbody>
			<tr>
					<th scope="row"><label><?php _e( 'Enable Dynamic Webhook', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<select class="regular-text" name="dynamic_webhook" id="dynamic_webhook">
								<option
									value="0"
									<?php echo $settings['dynamic_webhook'] ? 'selected' : '' ?>
								>
									No
								</option>
								<option
									value="1"
									<?php echo $settings['dynamic_webhook'] ? 'selected' : '' ?>
								>
									Yes
								</option>
							</select>
							<p class="description">
								<?php
									echo __( 'If enabled, charge and refund events will be automatically set to be received at the URL below. This can be useful when you need multiple webhook endpoints on the same account. ' );
								?>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php _e( 'Webhook endpoint', 'omise' ); ?></label></th>
					<td>
						<fieldset>
							<code><?php echo get_rest_url( null, 'omise/webhooks' ); ?></code>
							<p class="description">
								<?php
									echo sprintf(
										wp_kses(
											__( 'Unless dynamic webhooks are enabled, you must add the URL above as a new endpoint on your <a href="%s">Opn Payments dashboard</a> (HTTPS only).', 'omise' ),
											[
												'a' => ['href' => []],
											],
										),
										esc_url( 'https://dashboard.omise.co/v2/settings/webhooks' )
									);
								?>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>

		<hr />

		<h3><?php _e( 'Payment Methods', 'omise' ); ?></h3>
		<?php if ($settings['account_country']) : ?>
			<!--  account_country exists when API key is valid -->
			<p><?php _e( 'The table below is a list of available payment methods for this Opn Payments account that you can enable in your WooCommerce store.', 'omise' ); ?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="sandbox"><?php _e( 'Available Payment Methods', 'omise' ); ?></label></th>
						<td>
							<table class="widefat fixed striped" cellspacing="0">
								<thead>
									<tr>
										<?php
											$columns = array(
												'name'    => __( 'Payment Method', 'omise' ),
												'status'  => __( 'Enabled', 'omise' ),
												'setting' => ''
											);

											foreach ( $columns as $key => $column ) {
												switch ( $key ) {
													case 'status' :
													case 'setting' :
														echo '<th style="text-align: center; padding: 10px;" class="' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
														break;

													default:
														echo '<th style="padding: 10px;" class="' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
														break;
												}

											}
										?>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( Omise()->payment_methods() as $gateway ) :
										$gateway = new $gateway;
										if ( $gateway->is_country_support( $settings['account_country'] ) && $gateway->is_capability_support( $available_payment_methods) ) :
											echo '<tr>';

											foreach ( $columns as $key => $column ) :
												switch ( $key ) {
													case 'name' :
														$method_title = $gateway->get_title() ? $gateway->get_title() : __( '(no title)', 'omise' );
														echo '<td class="name">
															<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . strtolower( $gateway->id ) ) . '">' . esc_html( $method_title ) . '</a>
														</td>';
														break;

													case 'status' :
														echo '<td class="status" style="text-align: center;">';
														echo ( 'yes' === $gateway->enabled ) ? '<span>' . __( 'Yes', 'omise' ) . '</span>' : '-';
														echo '</td>';
														break;

													case 'setting' :
														echo '<td class="setting" style="text-align: center;">
															<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . strtolower( $gateway->id ) ) . '">' . __( 'config', 'omise' ) . '</a>
														</td>';
														break;

													default:
														break;
												}
											endforeach;

											echo '</tr>';

										endif;
									endforeach;
									?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		<?php else: ?>
			<p><?php _e( 'Please set up your Omise account to see all the available payment methods.', 'omise' ); ?></p>
		<?php endif; ?>

		<input type="hidden" name="omise_setting_page_nonce" value="<?= wp_create_nonce( 'omise-setting' ); ?>" />
		<?php submit_button( __( 'Save Settings', 'omise' ) ); ?>

	</form>
</div>
