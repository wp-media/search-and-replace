<?php
/**
 * Template for displaying sql import page
 */
// Prevent direct access.
if ( ! defined( 'SEARCH_REPLACE_BASEDIR' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
?>

	<form action="" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<tbody>
			<tr>
				<th><strong><?php esc_html_e( 'Select SQL file to upload. ', 'search-and-replace' ); ?></strong></th>

				<td><input type="file" name="file_to_upload" id="file_to_upload"></td>
			</tr>
			<tr><th></th><td><?php esc_html_e( 'Maximum file size: ', 'search-and-replace' );echo $this->file_upload_max_size().'KB'; ?></td></tr>
			</tbody>
		</table>
		<?php $this->show_submit_button(); ?>
	</form>