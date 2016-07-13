<?php defined( 'ABSPATH' ) or die ( "No direct script access allowed." ); ?>

<div class='wrap'>
	<?php if ( isset( $viewData['balance'] ) ) : ?>

		<?php Omise_Util::render_partial( 'header', $viewData ); ?>

		<h1>Transactions History</h1>

		<h2 class="nav-tab-wrapper wp-clearfix">
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'omise-plugin-admin-page' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab<?php if ( ! isset( $_GET['action'] ) || isset( $_GET['action'] ) && 'locations' != $_GET['action'] ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Charges' ); ?></a>
		</h2>

		<div id="Omise-ChargeList">
			<form method="get">
				<input type="hidden" name="page" value="omise-plugin-admin-page" />
				<?php
				$charge_table = new Omise_Charges_Table( $viewData["charges"] );
				$charge_table->prepare_items();
				$charge_table->display();
				?>
			</form>
		</div>
	<?php endif; ?>
</div>