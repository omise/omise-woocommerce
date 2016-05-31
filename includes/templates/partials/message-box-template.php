<?php if ( '' !== $partial_data['message_type'] && '' !== $partial_data['message'] ) : ?>
	<div class='<?php echo $partial_data['message_type']; ?>'>
		<p><?php echo esc_html( $partial_data['message'] ); ?></p>
	</div>
<?php endif; ?>