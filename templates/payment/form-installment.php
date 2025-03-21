<?php 
// @codeCoverageIgnoreStart
if ( ! empty( $viewData['installments_enabled'] ) ) : ?>
	<div id="omise-installment" style="width:100%; max-width: 400px;"></div>
	<script>
		window.OMISE_UPDATED_CART_AMOUNT= `<?php echo $viewData['total_amount']; ?>`;
		window.LOCALE = `<?php echo get_locale(); ?>`;
		window.OMISE_CUSTOM_FONT_OTHER = 'Other';
	</script>
<?php else: ?>
	<p>
		<?php 
			if(get_woocommerce_currency() === 'THB') {
				echo __( "There are no installment plans available for this purchase amount (minimum amount is {$viewData['installment_min_limit']} THB).", 'omise' ); 
			} else {
				echo __( 'Purchase Amount is lower than the monthly minimum payment amount.', 'omise' );
			}
		?>
	</p>
<?php endif; 
// @codeCoverageIgnoreEnd
?>
