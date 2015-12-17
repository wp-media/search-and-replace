<?php
/**
 * Template for displaying sql export page
 */
// Prevent direct access.
if ( ! defined( 'INSR_DIR' ) ) {
	//exit;
}
?>
<div class="wrap">

	<h1 id="title"><?php _e( 'Inpsyde Search Replace', 'insr' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>/tools.php?page=sql_export"><?php _e( 'Export SQL dump', 'insr' ); ?></a>
		<a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=inpsyde_search_replace"><?php _e( 'Search and replace', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php echo admin_url() ?>/tools.php?page=sql_import"><?php _e( 'Import SQL file', 'insr' ); ?></a>
	</h2>

	<form action="" method="post">
		<table class="form-table">
			<tbody>
			<tr>
				<th><strong><?php _e( 'Search for: ', 'insr' ); ?></strong></th>
				<td><input type="text" name="search" value="<?php $this->show_site_url(); ?>" /></td>
			</tr>
			<tr>
				<th><strong><?php _e( 'Replace with: ', 'insr' ); ?></strong></th>
				<td><input type="text" name="replace" placeholder="<?php _e( 'New URL' ) ?>" /></td>
			</tr>
			</tbody>
		</table><?php $this->show_submit_button(); ?></form>

