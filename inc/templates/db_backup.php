<?php
/**
 * Template for displaying sql export page
 */
// Prevent direct access.
if ( ! defined( 'SEARCH_REPLACE_BASEDIR' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
?>

	<p><?php esc_html_e(
			'Create a backup of your database by clicking "Create SQL File".',
			'search-and-replace' ); ?>
	</p>

	<form action="" method="post">
		<?php $this->show_submit_button(); ?>
	</form>