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
		<a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>/tools.php?page=sql_export"><?php _e( 'Backup Database', 'insr' ); ?></a>
		<a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=replace_domain"><?php _e( 'Replace Domain URL', 'insr' ); ?></a>
		<a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=inpsyde_search_replace"><?php _e( 'Search and replace', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php echo admin_url() ?>/tools.php?page=sql_import"><?php _e( 'Import SQL file', 'insr' ); ?></a>
	</h2>

	<p><?php _e( 'Create a backup of your Database by clicking "Create SQL File".',
	             'insr' ); ?> </p>

	<form action="" method="post">


		</table><?php $this->show_submit_button(); ?></form>

