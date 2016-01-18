<?php
/**
 * Template for displaying sql import page
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
		<a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=replace_domain"><?php _e( 'Replace Domain URL', 'insr' ); ?></a><a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=inpsyde_search_replace"><?php _e( 'Search and replace', 'insr' ); ?></a>
		<a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>/tools.php?page=sql_import"><?php _e( 'Import SQL file', 'insr' ); ?></a>
	</h2>

	<form action="" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<tbody>
			<tr>
				<th><strong><?php _e( 'Select SQL file to upload. ', 'insr' ); ?></strong></th>

				<td><input type="file" name="file_to_upload" id="file_to_upload"></td>
			</tr>
			<tr><th></th><td><?php _e( 'Maximum file size: ', 'insr' );echo $this->file_upload_max_size().'KB'; ?></td></tr>
			</tbody>
			<tr>
				<td><?php $this->show_submit_button(); ?></td>
			</tr>
		</table>
	</form>


