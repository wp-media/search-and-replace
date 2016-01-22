<?php
/**
 * Template for displaying replace domain page
 */
// Prevent direct access.
if ( ! defined( 'INSR_DIR' ) ) {
	//exit;
}
?>
<div class="wrap">

	<h1 id="title"><?php _e( 'Inpsyde Search Replace', 'insr' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=db_backup"><?php _e( 'Backup Database', 'insr' ); ?></a>
		<a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>/tools.php?page=replace_domain"><?php _e( 'Replace Domain URL', 'insr' ); ?></a>
		<a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=inpsyde_search_replace"><?php _e( 'Search and replace', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php echo admin_url() ?>/tools.php?page=sql_import"><?php _e( 'Import SQL file', 'insr' ); ?></a>
	</h2>

	<p><?php _e( 'If you want to migrate your site to another domain, enter the new URL in the field "Replace with" and create a backup of your database by clicking "Create SQL File".',
	             'insr' ); ?> </p>

	<form action="" method="post">

		<table class="form-table">
			<tbody>

			<tr>
				<th><strong><?php _e( 'Search for: ', 'insr' ); ?></strong></th>
				<td><input type="text" " name="search" value="<?php echo get_site_url(); ?>" /></td>
			</tr>
			<tr>
				<th><strong><?php _e( 'Replace with: ', 'insr' ); ?></strong></th>
				<td><input type="text"  name="replace"  placeholder="<?php _e( 'New URL' ) ?>" /></td>
			</tr>
			</tbody>
		</table><?php $this->show_submit_button(); ?></form>

