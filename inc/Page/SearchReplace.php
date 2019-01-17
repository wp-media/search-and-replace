<?php

namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database;
use Inpsyde\SearchReplace\FileDownloader;

/**
 * Class SearchReplace
 *
 * @package Inpsyde\SearchReplace\Page
 */
class SearchReplace extends AbstractPage implements PageInterface {

	/**
	 * @var Manager
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

		require_once __DIR__ . '/../templates/search-replace.php';

		wp_localize_script(
			'insr-js',
			'insr_data_obj', [
				'search_matches_site_url' => __(
					'Your search contains your current site url. Replacing your site url can cause your site to break. Are you sure you wish to proceed?',
					'search-and-replace'
				),
				'site_url'                => $this->get_stripped_site_url(),
			]
		);
	}

	/**
	 * Returns the site url, strips http:// or https://
	 */
	private function get_stripped_site_url() {

		$url = get_site_url();

		return substr( $url, strpos( $url, '/' ) + 2 );
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return esc_html__( 'Search & Replace', 'search-and-replace' );
	}

	/**
	 * Return the static slug string.
	 *
	 * @return string
	 */
	public function get_slug() {

		return 'search-replace';
	}

	/**
	 * @return bool
	 * @throws \Throwable
	 */
	public function save() {

		if ( ! $this->is_request_valid() ) {
			return false;
		}

		// Retrieve tables.
		$tables = $this->selected_tables();
		if ( ! $tables ) {
			return false;
		}

		// @codingStandardsIgnoreLine
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? true : false;

		// remove wp_magic_quotes
		$search  = stripslashes( filter_input( INPUT_POST, 'search' ) );
		$replace = stripslashes( filter_input( INPUT_POST, 'replace' ) );
		$csv     = stripslashes( filter_input( INPUT_POST, 'csv' ) );
		$csv     = ( $csv === '' ? null : $csv );

		// Do not perform anything if we haven't anything.
		if ( ( ! $search && ! $replace ) && ! $csv ) {
			$this->add_error( esc_html__( 'You must provide at least a search string or a csv data', 'search-and-replace' ) );
			return false;
		}

		// If dry run is checked we run the replace function with dry run and return
		if ( true === $dry_run ) {
			$this->run_replace( $search, $replace, $tables, $dry_run, $csv );

			return false;
		}

		$export_or_save = filter_input( INPUT_POST, 'export_or_save' );

		if ( 'export' === $export_or_save ) {
			// 'export'-button was checked
			$report = $this->dbe->db_backup( $search, $replace, $tables, false, '', $csv );
			$this->downloader->show_modal( $report );
		} else {
			// "Save changes to database" was checked
			$this->run_replace( $search, $replace, $tables, $dry_run, $csv );
		}

		return true;
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

			return false;
		}

		$search  = filter_input( INPUT_POST, 'search' );
		$replace = filter_input( INPUT_POST, 'replace' );

		// if search field is empty and replace field is not empty quit. If both fields are empty, go on (useful for backup of single tables without changing)
		if ( '' === $search && '' !== $replace ) {
			$this->add_error( esc_attr__( 'Search field is empty.', 'search-and-replace' ) );

			return false;
		}

		return true;
	}

	/**
	 * Calls run_replace_table()  on each table provided in array $tables.
	 *
	 * @param string $search
	 * @param string $replace
	 * @param array  $tables  Array of tables we want to search.
	 * @param bool   $dry_run True if dry run (no changes are written to db).
	 * @param bool   $csv
	 *
	 * @throws \Throwable
	 */
	protected function run_replace( $search, $replace, $tables, $dry_run, $csv = null ) {

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
			$this->add_error( $report->get_error_message() );
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
				&& in_array( $table, $selected_tables, false )
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
		echo '</select>';
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Do Search & Replace', 'search-and-replace' );
	}

	/**
	 * Shows the search value in template.
	 */
	private function get_search_value() {

		$search  = isset( $_POST[ 'search' ] ) ? $_POST[ 'search' ] : '';
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? true : false;

		if ( $dry_run ) {
			$search = stripslashes( $search );
			$search = htmlentities( $search );
			echo $search;
		}

	}

	/**
	 * Shows the replace value in template
	 */
	private function get_replace_value() {

		$replace = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? true : false;
		if ( $dry_run ) {
			$replace = stripslashes( $replace );
			$replace = htmlentities( $replace );
			echo $replace;
		}

	}

	/**
	 * Shows the csv value in template.
	 */
	private function get_csv_value() {

		$csv     = isset( $_POST[ 'csv' ] ) ? $_POST[ 'csv' ] : '';
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? true : false;
		if ( $dry_run ) {
			$csv = stripslashes( $csv );
			$csv = htmlentities( $csv );
			echo $csv;
		}

	}

	/**
	 * Retrieve Selected Tables
	 *
	 * @return array The tables list from the POST request
	 */
	private function selected_tables() {

		$tables = [];

		if ( ! empty( $_POST[ 'select_tables' ] ) ) {
			$tables = filter_var( $_POST[ 'select_tables' ], FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		}

		return $tables;
	}

}
