<?php
/**
 * Template for displaying search & replace page
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
		<a class="nav-tab " href="<?php echo admin_url() ?>/tools.php?page=replace_domain"><?php _e( 'Replace Domain URL', 'insr' ); ?></a>
		<a class="nav-tab nav-tab-active" href="<?php echo admin_url() ?>/tools.php?page=inpsyde_search_replace">
			<?php _e( 'Search and replace', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php echo admin_url() ?>/tools.php?page=sql_import"><?php _e( 'Import SQL file', 'insr' ); ?></a>
	</h2>


	<form action="" method="post">
		<table class="form-table">
			<tbody>
			<tr>
				<th><strong><?php _e( 'Search for: ', 'insr' ); ?></strong></th>
				<td><input type="text" name="search" value="<?php $this->get_search_value() ?>" /></td>
			</tr>
			<tr>
				<th><strong><?php _e( 'Replace with: ', 'insr' ); ?></strong></th>
				<td><input type="text" name="replace" value="<?php $this->get_replace_value() ?>" /></td>
			</tr>
			<tr>

				<th><strong><?php _e( 'Select tables', 'insr' ); ?></strong></th>
				<td><?php $this->show_table_list(); ?><br>
					<br><input id="select_all_tables" type="checkbox" name="select_all" /><label for="select_all">
						<?php _e( 'Select all tables', 'insr' ) ?></label>
				</td>
			</tr>

			<tr>

				<th><strong><?php _e( 'Dry Run', 'insr' ); ?></strong></th>
				<td><input type="checkbox" id="dry_run" name="dry_run" checked /></td>
			</tr>
			<tr class="maybe_disabled disabled">
				<th><?php _e( 'Export SQL file or write changes to DB?', 'insr' ) ?></th>
				<td><input id="radio1" type="radio" name="export_or_save" value="export" checked disabled />
					<label for="radio1"><?php _e( 'Export SQL file with changes', 'insr' ) ?></label>


					<br><input id="radio2" type="radio" name="export_or_save" value="save_to_db" disabled />
					<label for="radio2"><?php _e( 'Save changes to Database', 'insr' ) ?></label></td>
			</tr>


			</tr>

			<tr class="maybe_disabled disabled">
				<th><label for="compress"><strong><?php _e( 'Use GZ compression', 'insr' ); ?></strong></label></th>
				<td><input type="checkbox" name="compress" disabled /></td>
			</tr>

			<tr>
				<th></th>
				<td><?php $this->show_submit_button(); ?></td>
			</tr>

			</tbody>
		</table>
	</form>

</div>


