<div class="wrap omise">
	<h1><?php echo $title; ?></h1>

	<hr class="wp-header-end">

	<h2 class="nav-tab-wrapper wp-clearfix">
		<?php $page->render_menu(); ?>
	</h2>

	<?php $page->render_content(); ?>
</div>
