<?php
// @codeCoverageIgnoreStart
$installments_enabled = isset( $viewData['installments_enabled'] ) && true === (bool) $viewData['installments_enabled'];
$show_installment_form = isset( $viewData['show_installment_form'] ) && true === (bool) $viewData['show_installment_form'];

if ( $installments_enabled ) : ?>
	<?php if ( $show_installment_form ) : ?>
		<div id="omise-installment" style="width:100%; max-width: 400px;"></div>
		<script>
			window.OMISE_UPDATED_CART_AMOUNT = <?php echo json_encode( (int) $viewData['total_amount'] ); ?>;
			window.LOCALE = <?php echo json_encode( (string) get_locale() ); ?>;
			window.OMISE_CUSTOM_FONT_OTHER = 'Other';
		</script>
	<?php endif; ?>
<?php else: ?>
	<p>
		<?php
			if ( get_woocommerce_currency() === 'THB' ) {
				echo sprintf(
					__( 'There are no installment plans available for this purchase amount (minimum amount is %s THB).', 'omise' ),
					htmlspecialchars( (string) $viewData['installment_min_limit'], ENT_QUOTES, 'UTF-8' )
				);
			} else {
				echo __( 'Purchase Amount is lower than the monthly minimum payment amount.', 'omise' );
			}
		?>
	</p>
<?php endif;
// @codeCoverageIgnoreEnd
?>
