<?php defined( 'ABSPATH' ) or die ( "No direct script access allowed." ); ?>

<div class='wrap'>
	<?php if ( isset( $viewData['balance'] ) ) : ?>

		<?php Omise_Util::render_partial( 'header', $viewData ); ?>

		<h1><?php echo Omise_Util::translate( 'Charges History' ); ?></h1>

		<div id="Omise-ChargeList">
			<form method="get">
				<input type="hidden" name="page" value="omise-plugin-admin-page" />
				<?php
				$charge_table = new Omise_Charges_Table( $viewData['charges'] );
				$charge_table->prepare_items();
				$charge_table->display();
				?>
			</form>
		</div>
	<?php endif; ?>
</div>