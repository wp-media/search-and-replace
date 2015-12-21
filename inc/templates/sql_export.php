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

	<p><?php _e( 'Create a backup of your Database by clicking "Create SQL File". Check "Change URL" and enter the new URL in the field "Replace with" if you want to migrate your site to another domain.',
	             'insr' ); ?> </p>

	<form action="" method="post">

		<table class="form-table">
			<tbody>
			<tr>
				<th><strong><?php _e( 'Change Site URL: ', 'insr' ); ?></strong></th>
				<td><input type="checkbox" id="change_url" name="change_url" /></td>
			</tr>
			<tr>
				<th><strong><?php _e( 'Search for: ', 'insr' ); ?></strong></th>
				<td><input type="text" class="maybe_disabled" name="search" disabled value="<?php $this->show_site_url(); ?>" /></td>
			</tr>
			<tr>
				<th><strong><?php _e( 'Replace with: ', 'insr' ); ?></strong></th>
				<td><input type="text" class="maybe_disabled" name="replace" disabled placeholder="<?php _e( 'New URL' ) ?>" /></td>
			</tr>
			</tbody>
		</table><?php $this->show_submit_button(); ?></form>

