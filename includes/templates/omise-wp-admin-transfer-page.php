<?php defined( 'ABSPATH' ) or die ( "No direct script access allowed." ); ?>

<div class='wrap'>
	<?php if ( isset( $viewData['balance'] ) ) : ?>

		<?php Omise_Util::render_partial( 'header', $viewData ); ?>

		<h1><?php echo Omise_Util::translate( 'Transfers History' ); ?></h1>

		<div id="Omise-TransferList">
			<form method="get">
				<input type="hidden" name="page" value="omise-plugin-admin-transfer-page" />
				<?php
				$transfer_table = new Omise_Transfers_Table( $viewData['transfers'] );
				$transfer_table->prepare_items();
				$transfer_table->display();
				?>
			</form>
		</div>
	<?php endif; ?>
</div>