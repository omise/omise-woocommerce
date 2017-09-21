<p>
	<?php
	echo sprintf(
		wp_kses(
			__( 'Note that you will need to setup <a href="%s">Omise Webhook endpoint</a> before enable the Chatbot feature,<br/>otherwise your Chatbot will not response properly.', 'omise' ),
			array(
				'br' => array(),
				'a'  => array( 'href' => array() )
			)
		),
		esc_url(
			add_query_arg(
				array( 'page' => $this->slug ),
				admin_url( 'admin.php' )
			)
		)
	);
	?>
</p>

<form method="POST">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="chatbot_enabled"><?php _e( 'Enable/Disable', 'omise' ); ?></label></th>
				<td>
					<fieldset>
						<label for="chatbot_enabled">
							<input name="chatbot_enabled" type="checkbox" id="chatbot_enabled" value="1" <?php echo 'yes' === $settings['chatbot_enabled'] ? 'checked="checked"' : ''; ?>>
							<?php _e( 'Enable Omise Chatbot feature.', 'omise' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="chatbot_available_time_from"><?php _e( 'Chatbot Available Time', 'omise' ); ?></label></th>
				<td>
					<fieldset>
						<input name="chatbot_available_time_from" type="text" id="chatbot_available_time_from" value="<?php echo $settings['chatbot_available_time_from']; ?>" placeholder="from: 00:00:00" /> - 
						<input name="chatbot_available_time_to" type="text" id="chatbot_available_time_to" value="<?php echo $settings['chatbot_available_time_to']; ?>" placeholder="to: 23:59:59" />
						<p class="description">
							<?php
							echo sprintf(
								wp_kses(
									__( 'Automatically enable/disable the Chatbot on a specific time. (in <code>H:i:s</code> format. Example : <code>23:59:59</code>).<br />Note that Chatbot will be operated based on your <a href="%s">store\'s timezone</a>.', 'omise' ),
									array(
										'a'    => array( 'href' => array() ),
										'br'   => array(),
										'code' => array()
									)
								),
								esc_url( admin_url( 'options-general.php' ) )
							);
							?>
						</p>
					</fieldset>
				</td>
			</tr>

		</tbody>
	</table>

	<hr />

	<h3><?php _e( 'Facebook Bot Settings', 'omise' ); ?></h3>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="chatbot_facebook_page_access_token"><?php _e( 'Facebook Page Access Token', 'omise' ); ?></label></th>
				<td>
					<fieldset>
						<input name="chatbot_facebook_page_access_token" type="password" id="chatbot_facebook_page_access_token" value="<?php echo $settings['chatbot_facebook_page_access_token']; ?>" class="regular-text" />
						<p class="description">
							<?php
							echo sprintf(
								wp_kses(
									__( 'Facebook Page Access Token can be found at <a href="%s">Facebook Messenger Product Setting page</a>.<br/>This is required field in order to start connecting to Facebook APIs.', 'omise' ),
									array(
										'a'    => array( 'href' => array() ),
										'br'   => array()
									)
								),
								esc_url( 'https://developers.facebook.com/docs/messenger-platform/guides/setup/#page_access_token' )
							);
							?>
						</p>
					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="chatbot_facebook_bot_callback_url"><?php _e( 'Facebook Bot Callback URL', 'omise' ); ?></label></th>
				<td>
					<fieldset>
						<code><?php echo get_rest_url( null, 'omise/chatbot/facebook' ); ?></code>
						<p class="description">
							<?php
							echo wp_kses(
								__( 'Copy the Callback URL and paste into the Webhook section on your Facebook app setting page.<br/>This url will be used to setup your Facebook application.', 'omise' ),
								array( 'br' => array() )
							)
							?>
						</p>
					</fieldset>
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="chatbot_facebook_bot_verify_token"><?php _e( 'Facebook Bot Verify Token', 'omise' ); ?></label></th>
				<td>
					<fieldset>
						<input name="chatbot_facebook_bot_verify_token" type="text" id="chatbot_facebook_bot_verify_token" value="<?php echo $settings['chatbot_facebook_bot_verify_token']; ?>" class="regular-text" />
						<p class="description">
							<?php
							echo sprintf(
								wp_kses(
									__( 'Facebook Bot Verify Token can be found at <a href="%s">Facebook Messenger Product Setting page</a>.', 'omise' ),
									array( 'a' => array( 'href' => array() ) )
								),
								esc_url( 'https://developers.facebook.com/docs/messenger-platform/guides/setup/#page_access_token' )
							);
							?>
						</p>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>

	<?php submit_button( __( 'Save Settings', 'omise' ) ); ?>

</form>
