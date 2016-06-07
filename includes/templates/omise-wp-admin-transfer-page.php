<?php defined( 'ABSPATH' ) or die ( "No direct script access allowed." ); ?>

<div class='wrap'>
	<?php if ( isset( $viewData['balance'] ) ) : ?>

		<?php require_once ('omise-wp-admin-page-main.php'); ?>

		<h1>Transfers History</h1>

		<h2 class="nav-tab-wrapper wp-clearfix">
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'omise-plugin-admin-transfer-page' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab nav-tab-active"><?php esc_html_e( 'Transfers' ); ?></a>
		</h2>

		<div id="Omise-TransferList">
			<?php
			$transfer_table = new Omise_Transfers_Table( $viewData["transfers"] );
			$transfer_table->prepare_items();
			$transfer_table->display();
			?>
		</div>
	<?php endif; ?>
</div>