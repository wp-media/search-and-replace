<?php
namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database\Exporter;
use Inpsyde\searchReplace\Database\Manager;
use Inpsyde\SearchReplace\Database\Replace;

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
	 * @var Replace
	 */
	private $replace;

	/**
	 * @var Exporter
	 */
	private $dbe;

	/**
	 * BackupDatabase constructor.
	 *
	 * @param Manager  $dbm
	 * @param Replace  $replace
	 * @param Exporter $dbe
	 */
	public function __construct( Manager $dbm, Replace $replace, Exporter $dbe ) {

		$this->dbm     = $dbm;
		$this->replace = $replace;
		$this->dbe     = $dbe;
	}

	/**
	 * shows the page contents
	 */
	public function render() {

		require_once(  __DIR__ . '/../templates/search_replace.php' );
	}

	/**
	 * @return string
	 */
	public function get_menu_title() {

		return __( 'Search & Replace', 'search-and-replace' );
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return __( 'Search & Replace', 'search-and-replace' );
	}

	/**
	 *prints a select with all the tables and their sizes
	 *
	 * @return void     *
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

	public function save() {

		//check for errors in form
		if ( ! $this->is_request_valid() ) {
			return;
		}

		$tables  = $_POST[ 'select_tables' ];
		$dry_run = isset( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;

		//remove wp_magic_quotes
		$search  = stripslashes( filter_input( INPUT_POST, 'search' ) );
		$replace = stripslashes( filter_input( INPUT_POST, 'replace' ) );

		//if dry run is checked we run the replace function with dry run and return
		if ( $dry_run == TRUE ) {
			$this->run_replace( $search, $replace, $tables, $dry_run );

			return;
		}

		$export_or_save = filter_input( INPUT_POST, 'export_or_save' );

		if ( 'export' === $export_or_save ) {
			//'export'-button was checked
			$this->dbe->create_backup_file( $search, $replace, $tables );
		} else {
			//"Save changes to database" was checked
			$this->run_replace( $search, $replace, $tables, $dry_run );

		}
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return __( 'Do Search & Replace', 'search-and-replace' );
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

		if ( count( $report[ 'changes' ] ) > 0 ) {
			$this->dbe->show_changes( $report );
		}

		//if no changes found report that
		if ( 0 === count( $report [ 'changes' ] ) ) {
			echo '<p>' . esc_html__( 'Search pattern not found.', 'search-and-replace' ) . '</p>';
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
		if ( $select_tables === '' ) {
			$this->errors->add( 'no_table_selected', esc_attr__( 'No Tables were selected.', 'search-and-replace' ) );

			return FALSE;
		}

		$search  = filter_input( INPUT_POST, 'search' );
		$replace = filter_input( INPUT_POST, 'replace' );

		//if search field is empty and replace field is not empty quit. If both fields are empty, go on (useful for backup of single tables without changing)
		if ( $search === '' && $replace === '' ) {
			$this->errors->add( 'empty_search', esc_attr__( 'Search field is empty.', 'search-and-replace' ) );

			return FALSE;
		}

		$export_or_save = filter_input( INPUT_POST, 'export_or_save' );
		//check if the user tries to replace domain name into the database
		if ( $export_or_save === '' || 'save_to_db' === $export_or_save ) {
			$contains_site_url = strpos( $search, $this->get_stripped_site_url() );
			if ( $contains_site_url !== FALSE ) {
				$this->errors->add(
					'URL_in-search_expression',
					esc_attr__(
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
			$search = stripslashes($search);
			$search = htmlentities ($search);
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
			$replace = stripslashes($replace);
			$replace = htmlentities ($replace);
			echo $replace;
		}

	}

	/**
	 * Trims a given string to 50 chars before and after the search string, if the string is longer than 199 chars.
	 *
	 * @param $needle    string
	 * @param $haystack  string
	 * @param $delimiter array  $delimiter[0]=start delimiter, $delimiter[1] = end delimiter
	 *
	 * @return string The trimmed $haystack
	 */
	protected function trim_search_results( $needle, $haystack, $delimiter ) {

		//if result has <200 characters we return the whole string
		if ( strlen( $haystack ) < 100 ) {
			return $haystack;
		}

		$trimmed_results = NULL;
		// Get all occurrences of $needle with up to 50 chars front & back.
		preg_match_all( '@.{0,50}' . $needle . '.{0,50}@', $haystack, $trimmed_results );
		$return_value = '';
		/** @var array $trimmed_results */
		$imax = count( $trimmed_results );
		for ( $i = 0; $i < $imax; $i ++ ) {
			//reset delimiter, might have been changed
			$local_delimiter = $delimiter;
			//check if the first trimmmed result is the beginning of $haystack. if so remove leading delimiter
			if ( $i === 0 ) {
				$pos = strpos( $haystack, $trimmed_results[ 0 ][ $i ] );
				if ( $pos === 0 ) {
					$local_delimiter[ 0 ] = '';
				}
			}

			//check if the last trimmed result is the end of $haystack. if so, remove trailing delimiter
			$last_index = count( $trimmed_results ) - 1;
			if ( $i === $last_index ) {
				$trimmed_result_length = strlen( $trimmed_results[ 0 ][ $i ] );
				$substring             = substr( $haystack, - $trimmed_result_length );
				if ( $substring === $trimmed_results[ 0 ][ $i ] ) {
					$local_delimiter[ 1 ] = '';
				}

			}
			$return_value .= $local_delimiter[ 0 ] . $trimmed_results[ 0 ][ $i ] . $local_delimiter[ 1 ];
		}

		return $return_value;
	}

}