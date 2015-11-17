<?php

namespace Inpsyde\SearchReplace\inc;

class Admin {

	/**
	 * @var DatabaseManager
	 * stores instance of DatabaseManager
	 */
	protected $dbm;

	public function __construct( $dbm, $replace ) {

		if ( ! $dbm instanceof DatabaseManager ) {
			throw new \InvalidArgumentException ( "Class Replace needs Object of Type DatabaseManager as Parameter" );

		}
		if ( ! $replace instanceof Replace ) {
			throw new \InvalidArgumentException ( "Class Replace needs Object of Type Replace as Parameter" );

		}

		$this->dbm     = $dbm;
		$this->replace = $replace;

		//add plugin menu & plugin css
		add_action( 'admin_menu', array( $this, 'register_plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_css' ) );

	}

	/**
	 *registers the Plugin stylesheet
	 */

	public function register_admin_css( $hook ) {

		//register on plugin admin page only
		if ( $hook == 'tools_page_inpsyde_search_replace' ) {

			$url    = ( INSR_DIR . '/css/inpsyde-search-replace.css' );
			$handle = "insr-styles";
			wp_register_script( $handle, $url );
			wp_enqueue_style( $handle, $url, array(), FALSE, FALSE );
		}
	}

	/**
	 *registers admin page
	 */
	public function register_plugin_menu() {

		//this sets the capability needed to access the menu
		//can be overridden by filter 'insr-capability'
		$cap = apply_filters( 'insr-capability', 'install_plugins' );

		$page = add_submenu_page( 'tools.php', __( 'Inpsyde Search & Replace', 'insr' ),
		                          __( 'Inpsyde Search & Replace', 'insr' ), $cap, 'inpsyde_search_replace',
		                          array( $this, 'show_dashboard' ) );

	}

	/**
	 *callback function for click on menu item
	 */
	public function show_dashboard() {

		//check if "search replace"-button was clicked

		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "search_replace" ) {
			$this->handle_search_replace_event();

		}
		require_once( 'templates/dashboard.php' );
	}

	/**
	 *prints a select with all the tables and their sizes
	 *
	 */
	protected function show_table_list() {

		$tables      = $this->dbm->get_tables();
		$sizes       = $this->dbm->get_sizes();
		$table_count = count( $tables );

		//adjust height of select according to table count, but max 20 rows
		$select_rows = $table_count < 20 ? $table_count : 20;

		echo '<select id="select_tables" name="select_tables[]" multiple="multiple"  size = "' . $select_rows . '">';
		foreach ( $tables as $table ) {
			$table_size = isset ( $sizes[ $table ] ) ? $sizes[ $table ] : '';
			echo "<option value='$table'>$table $table_size</option>option>";

		}

	}

	protected function handle_search_replace_event() {

		if ( isset ( $_POST[ 'select_tables' ] ) ) {
			$tables = ( $_POST[ 'select_tables' ] );

			//if no table is selected display error
		} else {
			$errors[] = __( 'No Tables were selected.', 'insr' );

		}
		if ( ! isset ( $_POST[ 'search' ] ) || $_POST[ 'search' ] == "" ) {
			$errors[] = __( 'Search field is empty.', 'insr' );
		}
		if ( isset ( $errors ) ) {
			$this->display_errors( $errors );

			return;
		}

		$dry_run = isset ( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;

		$this->run_replace( $_POST[ 'search' ], $_POST[ 'replace' ], $tables, $dry_run );

	}

	protected function show_submit_button() {

		wp_nonce_field( 'do_search_replace', 'insr_nonce' );
		$value = translate( "Do Search / Replace", "insr" );

		$html = '	<input type="hidden" name="action" value="search_replace" /><input id ="insr_submit"type="submit" value="' . $value . ' "class="button" />';

		echo $html;
	}

	/**
	 *
	 * calls run_replace_table()  on each table provided in array $tables
	 *
	 * @param $search
	 * @param $replace
	 * @param $tables
	 * @param $dry_run
	 */
	protected function run_replace( $search, $replace, $tables, $dry_run ) {

		$no_matches = TRUE;

		echo '<div class = "updated">';
		if ( $dry_run ) {
			echo '<p><strong>' . __( 'Dry run is selected. No changes will be made to the database.',
			                         'insr' ) . '</strong></p>';

		} else {
			echo '<p><strong>' . __( 'The following changes were made to the database: ',
			                         'insr' ) . '</strong></p>';
		}
		$this->replace->set_dry_run( $dry_run );

		foreach ( $tables as $table ) {

			$result = $this->run_replace_table( $search, $replace, $table );
			if ( $result !== FALSE ) {
				echo $result;
				//if a match was found we set no matches to false
				$no_matches = FALSE;
			}

		}

		//report if no matches were found
		$html = $no_matches ? __( 'Search pattern not found.', 'insr' ) . '</div>' : '</div>';
		echo $html;

	}

	/**
	 * runs search replace on the table in $table
	 * returns a html-formatted string with the changes on success, FALSE if no changes were found
	 *
	 * @param $search
	 * @param $replace
	 * @param $table
	 *
	 * @return bool|string
	 *
	 *
	 */
	protected function run_replace_table( $search, $replace, $table ) {

		$results = $this->replace->replace_values( $search, $replace, $table );
		$changes = $results[ 'changes' ];
		if ( count( $changes ) > 0 ) {
			$html = '<table class = "widefat fixed"><thead><strong>Table: </strong>' . $table . '</thead>';

			foreach ( $changes as $change ) {
				$html = $html . '<tr>';
				$html = $html . '<th>' . __( 'row',
				                             'insr' ) . '</th><td>' . $change [ 'row' ] . '</td><th> ' . __( 'column',
				                                                                                             'insr' ) . '</th><td>' . $change [ 'column' ] . '</td> ';
				$html = $html . '<th>' . __( 'Old value:',
				                             'insr' ) . '</th><td>' . $change [ 'from' ] . '</th><td>' . '</td><th> ' . __( 'New value:',
				                                                                                                            'insr' ) . '</th><td>' . $change[ 'to' ] . '</td>';
				$html = $html . '</tr>';
			}
			$html = $html . '</table>';

			return $html;
		}

		return FALSE;

	}

	/**
	 * echoes the content of the $errors array as formatted HTML
	 *
	 * @param $errors
	 */
	protected function display_errors( $errors ) {

		echo '<div class = "error"><strong>' . __( 'Errors:', 'insr' ) . '</strong><ul>';
		foreach ( $errors as $error ) {
			echo '<li>' . $error . '</li>';
		}
		echo '</ul></div>';
	}

}