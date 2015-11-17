<?php
/**
 * Template for displaying the dashboard
 */
// Prevent direct access.

if ( ! defined( 'INSR_DIR' ) ) {
	exit;
}
?>

<div class="wrap">

	<h1 id="title"><?php _e( 'Inpsyde Search Replace', 'insr' ); ?></h1>

	<form action ="" method="post">
		<table class="form-table">
			<tbody>
			<tr>
				<th><label for="search"><strong><?php _e( 'Search for: ', 'insr' ); ?></strong></label></th>
				<td><input type="text" name="search" /></td>
			</tr>
			<tr>
				<th><label for="search"><strong><?php _e( 'Replace with: ', 'insr' ); ?></strong></label></th>
				<td><input type="text" name="replace" /></td>
			</tr>
			<tr>

				<th><label for="select_tables"><strong><?php _e( 'Select tables', 'insr' ); ?></strong></label></th>
				<td><?php $this->show_table_list(); ?></td>
			</tr>

			<tr>

				<th><label for="dry_run"><strong><?php _e( 'Dry Run', 'insr' ); ?></strong></label></th>
				<td><input type="checkbox" name=dry_run checked/> </td>
			</tr>

			<tr><th></th><td><?php $this->show_submit_button()?></td></tr>

			</tbody>
		</table>

</div>


