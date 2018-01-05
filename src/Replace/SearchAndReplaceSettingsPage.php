<?php

namespace Inpsyde\SearchAndReplace\Replace;

use Inpsyde\SearchAndReplace\Database;
use Inpsyde\SearchAndReplace\FileDownloader;
use Inpsyde\SearchAndReplace\Settings\AbstractPage;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Replace
 */
class SearchAndReplaceSettingsPage extends AbstractPage implements SettingsPageInterface {

	/**
	 * @var SettingsManager
	 */
	private $dbm;

	/**
	 * @var $replace
	 */
	private $replace;

	/**
	 * @var $dbe
	 */
	private $dbe;

	/**
	 * @var FileDownloader
	 */
	private $downloader;

	/**
	 * BackupDatabase constructor.
	 *
	 * @param Database\Manager  $dbm
	 * @param Database\Replace  $replace
	 * @param Database\Exporter $dbe
	 * @param FileDownloader    $downloader
	 */
	public function __construct( Database\Manager $dbm, Database\Replace $replace, Database\Exporter $dbe, FileDownloader $downloader ) {

		$this->dbm        = $dbm;
		$this->replace    = $replace;
		$this->dbe        = $dbe;
		$this->downloader = $downloader;
	}

	/**
	 * Shows the page contents
	 */
	public function render() {

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
						<input id="search" type="text" name="search" value="<?php $this->get_search_value() ?>" />
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

		<?php

		wp_localize_script(
			'insr-js',
			'insr_data_obj', array(
				'search_matches_site_url' => __(
					'Your search contains your current site url. Replacing your site url can cause your site to break. Are you sure you wish to proceed?',
					'search-and-replace'
				),
				'site_url'                => $this->get_stripped_site_url(),
			)
		);
	}

	/**
	 * Shows the replace value in template
	 */
	private function get_replace_value() {

		$replace = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;
		if ( $dry_run ) {
			$replace = stripslashes( $replace );
			$replace = htmlentities( $replace );
			echo $replace;
		}
	}

	/**
	 * Shows the search value in template.
	 */
	private function get_search_value() {

		$search  = isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : '';
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;
		if ( $dry_run ) {
			$search = stripslashes( $search );
			$search = htmlentities( $search );
			echo $search;
		}
	}

	/**
	 * Shows the csv value in template.
	 */
	private function get_csv_value() {

		$csv     = isset( $_POST[ 'csv' ] ) ? $_POST[ 'csv' ] : '';
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;
		if ( $dry_run ) {
			$csv = stripslashes( $csv );
			$csv = htmlentities( $csv );
			echo $csv;
		}
	}

	/**
	 * Returns the site url, strips http:// or https://
	 */
	private function get_stripped_site_url() {

		$url = get_site_url();

		return substr( $url, strpos( $url, '/' ) + 2 );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {

		return esc_html__( 'Search & Replace', 'search-and-replace' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_slug() {

		return 'search-replace';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Do Search & Replace', 'search-and-replace' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( array $request_data = [] ) {

		if ( ! $this->is_request_valid() ) {
			return FALSE;
		}

		// Retrieve tables.
		$tables = $this->selected_tables();
		if ( ! $tables ) {
			return FALSE;
		}

		// @codingStandardsIgnoreLine
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;

		// remove wp_magic_quotes
		$search  = stripslashes( filter_input( INPUT_POST, 'search' ) );
		$replace = stripslashes( filter_input( INPUT_POST, 'replace' ) );
		$csv     = stripslashes( filter_input( INPUT_POST, 'csv' ) );
		$csv     = ( $csv === '' ? NULL : $csv );

		// if dry run is checked we run the replace function with dry run and return
		if ( TRUE === $dry_run ) {
			$this->run_replace( $search, $replace, $tables, $dry_run, $csv );

			return FALSE;
		}

		$export_or_save = filter_input( INPUT_POST, 'export_or_save' );

		if ( 'export' === $export_or_save ) {
			// 'export'-button was checked
			$report = $this->dbe->db_backup( $search, $replace, $tables, FALSE, '', $csv );
			$this->downloader->show_modal( $report );
		} else {
			// "Save changes to database" was checked
			$this->run_replace( $search, $replace, $tables, $dry_run, $csv );
		}

		return TRUE;
	}

	/**
	 * Checks the input form and writes possible errors to a WP_Error object
	 *
	 * @return bool true|false
	 */
	protected function is_request_valid() {

		// If not table are selected mark the request as invalid but let user know why.
		if ( ! $this->selected_tables() ) {
			$this->add_error(
				esc_html__(
					'No Tables were selected. You must select at least one table to perform the action.',
					'search-and-replace'
				)
			);

			return FALSE;
		}

		$search  = filter_input( INPUT_POST, 'search' );
		$replace = filter_input( INPUT_POST, 'replace' );

		// if search field is empty and replace field is not empty quit. If both fields are empty, go on (useful for backup of single tables without changing)
		if ( '' === $search && '' !== $replace ) {
			$this->add_error( esc_attr__( 'Search field is empty.', 'search-and-replace' ) );

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Calls run_replace_table()  on each table provided in array $tables
	 *
	 * @param string $search
	 * @param string $replace
	 * @param array  $tables  array of tables we want to search
	 * @param bool   $dry_run True if dry run (no changes are written to db)
	 * @param bool   $csv
	 *
	 * @return null
	 */
	protected function run_replace( $search, $replace, $tables, $dry_run, $csv = NULL ) {

		echo '<div class="updated notice is-dismissible">';
		if ( $dry_run ) {
			echo '<p><strong>'
				. esc_html__(
					'Dry run is selected. No changes were made to the database and no SQL file was written .',
					'search-and-replace'
				)
				. '</strong></p>';

		} else {
			echo '<p><strong>'
				. esc_html__(
					'The following changes were made to the database: ',
					'search-and-replace'
				)
				. '</strong></p>';
		}
		$this->replace->set_dry_run( $dry_run );

		$report = $this->replace->run_search_replace( $search, $replace, $tables, $csv );

		if ( is_wp_error( $report ) ) {
			$this->add_error( __( $report->get_error_message(), 'search-and-replace' ) );
			$this->display_errors();
		} else {

			if ( count( $report[ 'changes' ] ) > 0 ) {
				$this->downloader->show_changes( $report );
			}

			// if no changes found report that
			if ( 0 === count( $report [ 'changes' ] ) ) {
				echo '<p>' . esc_html__( 'Search pattern not found.', 'search-and-replace' ) . '</p>';
			}
		}

		echo '</div>';

	}

	/**
	 * Prints a select with all the tables and their sizes
	 *
	 * @return void
	 */
	protected function show_table_list() {

		$tables      = $this->dbm->get_tables();
		$sizes       = $this->dbm->get_sizes();
		$table_count = count( $tables );

		// adjust height of select according to table count, but max 20 rows
		$select_rows = $table_count < 20 ? $table_count : 20;
		// if we come from a dry run, we select the tables to the dry run again
		$selected_tables = $this->selected_tables();

		echo '<select id="select_tables" name="select_tables[]" multiple="multiple"  size = "' . $select_rows . '">';
		foreach ( $tables as $table ) {
			$table_size = isset ( $sizes[ $table ] ) ? $sizes[ $table ] : '';
			// check if dry run. if dry run && current table is in "selected" array add selected attribute
			$selected = ( isset( $_POST[ 'dry_run' ] )
				&& $selected_tables
				&& in_array( $table, $selected_tables, FALSE )
			)
				? 'selected="selected"'
				: '';

			printf(
				"<option value='%s' %s>%s</option>",
				esc_attr( $table ),
				$selected,
				esc_html( $table . $table_size )
			);

		}
		echo( '</select>' );
	}

	/**
	 * Retrieve Selected Tables
	 *
	 * @return array The tables list from the POST request
	 */
	private function selected_tables() {

		$tables = array();

		if ( ! empty( $_POST[ 'select_tables' ] ) ) {
			$tables = filter_var( $_POST[ 'select_tables' ], FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		}

		return $tables;
	}
}
