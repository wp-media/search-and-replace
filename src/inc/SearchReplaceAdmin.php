<?php

namespace Inpsyde\SearchReplace\inc;

class SearchReplaceAdmin extends Admin {

	/**
	 *shows the page contents
	 */
	public function show_page() {

		//check if "search replace"-button was clicked

		if ( isset ( $_POST[ 'action' ] ) && 'search_replace' === $_POST[ 'action' ]
		     && check_admin_referer( 'do_search_replace', 'insr_nonce' )
		) {
			$this->handle_search_replace_event();

		}
		require_once( 'templates/search_replace.php' );
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
				echo "<option value='$table' selected='selected'>$table $table_size </option>";

				//if current table had not been selected echo option without "selected" attribute
			} else {
				echo '<option value="' . $table . '">' . $table . $table_size . '</option>';
			}

		}
		echo( '</select>' );
	}

	/**
	 *handles click on search replace, check input form and runs either create_backup() or run_replace() functions in this class
	 */
	protected function handle_search_replace_event() {

		$tables = '';

		//check for errors in form

		$this->check_input_form();
		if ( '' !== $this->errors->get_error_code() ) {
			$this->display_errors();

			return;
		}
		if ( isset ( $_POST[ 'select_tables' ] ) ) {
			$tables = $_POST[ 'select_tables' ];
		}

		$dry_run = isset( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;

		//remove wp_magic_quotes
		$search =  stripslashes($_POST[ 'search' ]);
		$replace =  stripslashes($_POST[ 'replace' ]);

		//if dry run is checked we run the replace function with dry run and return
		if ( $dry_run == TRUE ) {
			$this->run_replace( $search, $replace, $tables, $dry_run );
			return;
		}

		//'export'-button was checked
		if ( isset ( $_POST[ 'export_or_save' ] ) && 'export' === $_POST [ 'export_or_save' ] ) {

			$this->create_backup_file( $search, $replace, $tables );
		} else {

			//"Save changes to database" was checked

			$this->run_replace( $search, $replace, $tables, $dry_run );

		}
	}

	/**
	 *displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'do_search_replace', 'insr_nonce' );

		$html = '<input type="hidden" name="action" value="search_replace" />';
		echo $html;
		submit_button( esc_attr__( 'Do Search & Replace', 'insr' ) );

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
				     'insr' )
			     . '</strong></p>';

		} else {
			echo '<p><strong>'
			     . esc_html__(
				     'The following changes were made to the database: ',
				     'insr' )
			     . '</strong></p>';
		}
		$this->replace->set_dry_run( $dry_run );

		$report = $this->replace->run_search_replace( $search, $replace, $tables );

		if ( count( $report[ 'changes' ] ) > 0 ) {
			$this->show_changes( $report );
		}

		//if no changes found report that
		if ( 0 === count( $report [ 'changes' ] ) ) {
			echo '<p>' . esc_html__( 'Search pattern not found.', 'insr' ) . '</p>';
		}
		echo '</div>';

	}

	/**
	 *checks the input form and writes possible errors to a WP_Error object
	 */
	protected function check_input_form() {

		if ( ! isset( $_POST[ 'select_tables' ] ) ) {

			$this->errors->add( 'no_table_selected', esc_attr__( 'No Tables were selected.', 'insr' ) );

		}

		//if search field is empty and replace field is not empty quit. If both fields are empty, go on (useful for backup of single tables without changing)
		if ( isset( $_POST[ 'replace' ] ) && '' !== $_POST[ 'replace' ]
		     && ( ! isset ( $_POST[ 'search' ] ) || '' === $_POST[ 'search' ] )
		) {
			$this->errors->add( 'empty_search', esc_attr__( 'Search field is empty.', 'insr' ) );

			return;
		}
		//check if the user tries to replace domain name into the database
		if ( isset( $_POST[ 'export_or_save' ] ) && 'save_to_db' === $_POST [ 'export_or_save' ] ) {
			$search            = $_POST[ 'search' ];
			$contains_site_url = strpos( $search, $this->get_stripped_site_url() );
			if ( $contains_site_url !== FALSE ) {
				$this->errors->add(
					'URL_in-search_expression',
					esc_attr__( 'Your search contains your current site url. Replacing your site url will most likely cause your site to break. If you want to change the URL (and you know what you doing), please use the export function and make sure you backup your database before reimporting the changed SQL.',
					            'insr' ) );
			}

		}

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

}