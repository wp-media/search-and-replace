<?php
namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database,
	Inpsyde\SearchReplace\Service;
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
	 * shows the page contents
	 */
	public function render() {

		require_once( __DIR__ . '/../templates/search_replace.php' );
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
	 *prints a select with all the tables and their sizes
	 *
	 * @return void 
	 */
	protected function show_table_list() {

		$tables      = $this->dbm->get_tables();
		$sizes       = $this->dbm->get_sizes();
		$table_count = count( $tables );

		//adjust height of select according to table count, but max 20 rows
		$select_rows = $table_count < 20 ? $table_count : 20;

		//if we come from a dry run, we select the tables to the dry run again
		/** @var bool | string $selected_tables */
		$selected_tables = FALSE;
		if ( isset( $_POST[ 'select_tables' ] ) ) {
			$selected_tables = $_POST[ 'select_tables' ];
		}

		echo '<select id="select_tables" name="select_tables[]" multiple="multiple"  size = "' . $select_rows . '">';
		foreach ( $tables as $table ) {
			$table_size = isset ( $sizes[ $table ] ) ? $sizes[ $table ] : '';
			//check if dry run. if dry run && current table is in "selected" array add selected attribute
			if ( isset( $_POST[ 'dry_run' ] )
				&& $selected_tables
				&& in_array( $table, $selected_tables, FALSE )
			) {
				echo "<option value='$table' selected='selected'>$table .  $table_size </option>";

				//if current table had not been selected echo option without "selected" attribute
			} else {
				echo '<option value="' . $table . '">' . $table . $table_size . '</option>';
			}

		}
		echo( '</select>' );
	}

	/**
	 * @return bool
	 */
	public function save() {

		//check for errors in form
		if ( ! $this->is_request_valid() ) {

			$this->display_errors();

			return FALSE;
		}

		$tables  = isset( $_POST[ 'select_tables' ] ) ? $_POST[ 'select_tables' ] : '';
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;

		//remove wp_magic_quotes
		$search  = stripslashes( filter_input( INPUT_POST, 'search' ) );
		$replace = stripslashes( filter_input( INPUT_POST, 'replace' ) );

		//if dry run is checked we run the replace function with dry run and return
		if ( TRUE === $dry_run ) {
			$this->run_replace( $search, $replace, $tables, $dry_run );

			return FALSE;
		}

		$export_or_save = filter_input( INPUT_POST, 'export_or_save' );

		if ( 'export' === $export_or_save ) {
			//'export'-button was checked
			$report = $this->dbe->db_backup( $search, $replace, $tables );
			$this->downloader->show_modal( $report );
		} else {
			//"Save changes to database" was checked
			$this->run_replace( $search, $replace, $tables, $dry_run );
		}
		
		return TRUE;
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Do Search & Replace', 'search-and-replace' );
	}

	/**
	 * calls run_replace_table()  on each table provided in array $tables
	 *
	 * @param $search
	 * @param $replace
	 * @param $tables  array of tables we want to search
	 * @param $dry_run True if dry run (no changes are written to db)
	 *
	 * @return null
	 */
	protected function run_replace( $search, $replace, $tables, $dry_run ) {

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

		$report = $this->replace->run_search_replace( $search, $replace, $tables );

		if ( is_wp_error( $report ) ) {
			$this->add_error( __( $report->get_error_message(), 'search-and-replace' ) );
			$this->display_errors();
		} else {

			if ( count( $report[ 'changes' ] ) > 0 ) {
				$this->downloader->show_changes( $report );
			}

			//if no changes found report that
			if ( 0 === count( $report [ 'changes' ] ) ) {
				echo '<p>' . esc_html__( 'Search pattern not found.', 'search-and-replace' ) . '</p>';
			}
		}

		echo '</div>';

	}

	/**
	 * checks the input form and writes possible errors to a WP_Error object
	 *
	 * @return bool true|false
	 */
	protected function is_request_valid() {

		$select_tables = filter_input( INPUT_POST, 'select_tables' );
		if ( '' === $select_tables ) {
			$this->add_error( __( 'No Tables were selected.', 'search-and-replace' ) );

			return FALSE;
		}

		$search  = filter_input( INPUT_POST, 'search' );
		$replace = filter_input( INPUT_POST, 'replace' );

		//if search field is empty and replace field is not empty quit. If both fields are empty, go on (useful for backup of single tables without changing)
		if ( '' === $search && '' === $replace ) {
			$this->add_error( esc_attr__( 'Search field is empty.', 'search-and-replace' ) );

			return FALSE;
		}

		$export_or_save = filter_input( INPUT_POST, 'export_or_save' );
		//check if the user tries to replace domain name into the database
		if ( '' === $export_or_save || 'save_to_db' === $export_or_save ) {
			$contains_site_url = strpos( $search, $this->get_stripped_site_url() );
			if ( FALSE !== $contains_site_url ) {
				$this->add_error(
					esc_html__(
						'Your search contains your current site url. Replacing your site url will most likely cause your site to break. If you want to change the URL (and you know what you doing), please use the export function and make sure you backup your database before reimporting the changed SQL.',
						'search-and-replace'
					)
				);

				return FALSE;
			}

		}



		return TRUE;
	}

	/**
	 * Returns the site url, strips http:// or https://
	 */
	private function get_stripped_site_url() {

		$url = get_site_url();

		return substr( $url, strpos( $url, '/' ) + 2 );
	}

	/**
	 * shows the search value in template.
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
	 * shows the replace value in template
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

}