<?php
/**
 * Template for displaying sql import page
 */
// Prevent direct access.
if ( ! defined( 'INSR_DIR' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
?>
<div class="wrap">

	<h1 id="title"><?php esc_html_e( 'Search & Replace', 'insr' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab " href="<?php echo admin_url() ?>tools.php?page=db_backup"><?php esc_html_e( 'Backup Database', 'insr' ); ?></a>
		<a class="nav-tab " href="<?php echo admin_url() ?>tools.php?page=replace_domain"><?php esc_html_e( 'Replace Domain/URL', 'insr' ); ?></a><a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=inpsyde_search_replace"><?php esc_html_e( 'Search and Replace', 'insr' ); ?></a>
		<a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>tools.php?page=sql_import"><?php esc_html_e( 'Import SQL file', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php echo admin_url() ?>tools.php?page=credits"><?php esc_html_e( 'Credits', 'insr' ); ?></a>
	</h2>

	<form action="" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<tbody>
			<tr>
				<th><strong><?php esc_html_e( 'Select SQL file to upload. ', 'insr' ); ?></strong></th>

				<td><input type="file" name="file_to_upload" id="file_to_upload"></td>
			</tr>
			<tr><th></th><td><?php esc_html_e( 'Maximum file size: ', 'insr' );echo $this->file_upload_max_size().'KB'; ?></td></tr>
			</tbody>
		</table>
		<?php $this->show_submit_button(); ?>
	</form>