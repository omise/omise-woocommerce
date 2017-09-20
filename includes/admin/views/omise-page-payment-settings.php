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
				<th scope="row"><label for="sandbox"><?php _e( 'Test mode', 'omise' ); ?></label></th>
				<td>
					<fieldset>
						<label for="sandbox">
							<input name="sandbox" type="checkbox" id="sandbox" value="1" <?php echo 'yes' === $settings['sandbox'] ? 'checked="checked"' : ''; ?>>
							<?php _e( 'Enabling test mode means that all your transactions will be performed under the Omise test account.', 'omise' ); ?>
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
				<th scope="row"><label><?php _e( 'Webhook endpoint', 'omise' ); ?></label></th>
				<td>
					<fieldset>
						<code><?php echo get_rest_url( null, 'omise/webhooks' ); ?></code>
						<p class="description">
							<?php
							echo sprintf(
								wp_kses(
									__( 'To enable <a href="%s">WebHooks</a> feature, you must setup an endpoint at <a href="%s"><strong>Omise dashboard</strong></a> by using the above url <em>(HTTPS only)</em>.', 'omise' ),
									array(
										'a'       => array( 'href' => array() ),
										'em'      => array(),
										'strong'  => array()
									)
								),
								esc_url( 'https://www.omise.co/api-webhooks' ),
								esc_url( 'https://dashboard.omise.co/test/webhooks/edit' )
							);
							?>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>

	<hr />

	<h3><?php _e( 'Payment Methods', 'omise' ); ?></h3>
	<p><?php _e( 'The table below is a list of available payment methods that you can enable in your WooCommerce store.', 'omise' ); ?></p>
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
							$available_gateways = array(
								new Omise_Payment_Alipay,
								new Omise_Payment_Creditcard,
								new Omise_Payment_Internetbanking
							);
							foreach ( $available_gateways as $gateway ) :

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
									}
								endforeach;

								echo '</tr>';

							endforeach;
							?>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>

	<?php submit_button( __( 'Save Settings', 'omise' ) ); ?>

</form>
