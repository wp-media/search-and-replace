<?php
/**
 * Template for displaying search & replace page
 */

// Prevent direct access.
if ( ! defined( 'SEARCH_REPLACE_BASEDIR' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
?>
<form action="" method="post">
	<table class="form-table">
		<tbody>
		<tr>
			<th>
				<label for="search">
					<?php esc_html_e( 'Search for: ', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<textarea id="search" type="text" name="search" rows="1"><?php $this->get_search_value() ?></textarea> 
			</td>
		</tr>
		<tr>
			<th>
				<label for="replace">
					<?php esc_html_e( 'Replace with: ', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<input id="replace" type="text" name="replace" value="<?php $this->get_replace_value() ?>" />
			</td>
		</tr>
		<tr>
			<th>
				<label for="csv">
					<?php esc_html_e( 'CSV Format Search/Replace:', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<textarea id="csv" cols="46" rows="5" name="csv" placeholder="<?php esc_html_e(
					'search value, replace value (one per line)',
					'search-and-replace'
				); ?>"><?php $this->get_csv_value(); ?></textarea>
				<p id="csv-hint">
					<?php esc_html_e(
						'Using comma delimited( , ). For example to replace cat with dog: cat,dog',
						'search-and-replace'
					); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><strong><?php esc_html_e( 'Select tables', 'search-and-replace' ); ?></strong></th>
			<td>
				<?php $this->show_table_list(); ?>
				<p>
					<input id="select_all_tables" type="checkbox" name="select_all" />
					<label for="select_all_tables">
						<?php esc_html_e( 'Select all tables', 'search-and-replace' ); ?>
					</label>
				</p>
			</td>
		</tr>

		<tr>
			<th>
				<label for="dry_run">
					<?php esc_html_e( 'Dry Run', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<input type="checkbox" id="dry_run" name="dry_run" checked />
			</td>
		</tr>
		<tr class="maybe_disabled disabled">
			<th>
				<?php esc_html_e( 'Export SQL file or write changes to DB?', 'search-and-replace' ); ?>
			</th>
			<td>
				<p>
					<input id="radio1" type="radio" name="export_or_save" value="export" checked disabled />
					<label for="radio1">
						<?php esc_html_e( 'Export SQL file with changes', 'search-and-replace' ); ?>
					</label>
				</p>
				<p>
					<input id="radio2" type="radio" name="export_or_save" value="save_to_db" disabled />
					<label for="radio2">
						<?php esc_html_e( 'Save changes to Database', 'search-and-replace' ); ?>
					</label>
				</p>
			</td>
		</tr>
		<tr class="maybe_disabled disabled">
			<th>
				<label for="compress">
					<?php esc_html_e( 'Use GZ compression', 'search-and-replace' ); ?>
				</label>
			</th>
			<td>
				<input id="compress" type="checkbox" name="compress" disabled />
			</td>
		</tr>

		</tbody>
	</table>
	<?php $this->show_submit_button( 'search-submit' ); ?>
</form>
