<?php
/**
 * Template for displaying the dashboard
 */
// Prevent direct access.
if ( ! defined( 'INSR_DIR' ) ) {
	//exit;
}
?>
<div class="wrap">

	<h1 id="title"><?php _e( 'Inpsyde Search Replace', 'insr' ); ?></h1>


	<form action="" method="post">
		<table class="form-table">
			<tbody>
			<tr>
				<th><strong><?php _e( 'Search for: ', 'insr' ); ?></strong></th>
				<td><input type="text" name="search" /></td>
			</tr>
			<tr>
				<th><strong><?php _e( 'Replace with: ', 'insr' ); ?></strong></th>
				<td><input type="text" name="replace" /></td>
			</tr>
			<tr>

				<th><strong><?php _e( 'Select tables', 'insr' ); ?></strong></th>
				<td><?php $this->show_table_list(); ?></td>
			</tr>

			<tr>

				<th><strong><?php _e( 'Dry Run', 'insr' ); ?></strong></th>
				<td><input type="checkbox" name="dry_run" checked /></td>
			</tr>
			<tr>
				<th><?php _e( 'Export SQL file or write changes to DB?', 'insr' ) ?></th>
				<td><input id="radio1" type="radio" name="export" value="true" checked /><label for="radio1"><?php _e( 'Export SQL file with changes', 'insr' ) ?></label>


					<br><input id="radio2" type="radio" name="export" value="false" /><label for="radio2"><?php _e( 'Save changes to Database', 'insr' ) ?></label></td>
			</tr>


			</tr>

			<tr>
				<th><label for="compress"><strong><?php _e( 'Use GZ compression', 'insr' ); ?></strong></label></th>
				<td><input type="checkbox" name="compress" /></td>
			</tr>

			<tr>
				<th></th>
				<td><?php $this->show_submit_button() ?></td>
			</tr>

			</tbody>
		</table>

</div>


