<?php

namespace Inpsyde\SearchReplace\inc;

class Admin {

	/**
	 * @var DatabaseManager
	 * stores instance of DatabaseManager
	 */
	protected $dbm;
	/**
	 * @var DatabaseExporter
	 */
	protected $dbe;
	/**
	 * @var Replace
	 */
	protected $replace;
	/**
	 * @var \WP_Error
	 */
	protected $errors;

	/**
	 * @param DatabaseManager  $dbm
	 * @param DatabaseExporter $dbe
	 * @param Replace          $replace
	 */
	public function __construct( DatabaseManager $dbm, DatabaseExporter $dbe, Replace $replace ) {

		$this->dbm     = $dbm;
		$this->dbe     = $dbe;
		$this->replace = $replace;
		$this->errors  = new \WP_Error();

		//if "download" was selected we have to check that early to prevent "headers already sent" error
		add_action( 'init', array( $this, 'deliver_backup_file' ) );

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

		add_submenu_page( 'tools.php', __( 'Inpsyde Search & Replace', 'insr' ),
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
	 * @return null
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

			echo "<option value='$table'>$table $table_size</option>";

		}
		echo( '</select>' );
	}

	/**
	 *handles click on search replace, check input form and runs either create_backup() or run_replace() functions in this class
	 */
	protected function handle_search_replace_event() {

		//'export'-button was checked
		if ( isset ( $_POST[ 'export' ] ) && $_POST [ 'export' ] == "true" ) {
			//check for errors in form
			$this->check_input_form();
			if ( $this->errors->get_error_code() != "" ) {
				$this->display_errors();

				return;

			}
			//check db again in dry run mode to show the changes that have been made

			$this->create_backup_file();

			return;
		} else {
			//"Save changes to database" was checked
			$this->check_input_form();

			if ( $this->errors->get_error_code() != "" ) {
				$this->display_errors();

				return;

			}
			$tables = ( $_POST[ 'select_tables' ] );

			$dry_run = isset ( $_POST[ 'dry_run' ] ) ? TRUE : FALSE;

			$this->run_replace( $_POST[ 'search' ], $_POST[ 'replace' ], $tables, $dry_run );

		}
	}

	/**
	 *checks input, creates a sql backup file, shows changes and download button
	 *
	 * @param void
	 *
	 * @return null
	 */
	protected function create_backup_file() {

		if ( isset ( $_POST[ 'select_tables' ] ) ) {
			$tables = ( $_POST[ 'select_tables' ] );

			$report = $this->dbe->db_backup( $_POST[ 'search' ], $_POST[ 'replace' ], $tables );
			echo '<div class = "updated">';
			//show changes if there are any
			foreach ( $report[ 'changes' ] as $table_report ) {
				$this->show_changes( $table_report );
			}

			//if no changes found report that
			if ( count( $report [ 'changes' ] ) == 0 ) {
				echo __( 'Search pattern not found.', 'insr' );
			}

			echo '</div>';

			//TODO: error handling

			$compress = ( isset ( $_POST[ 'compress' ] ) && $_POST [ 'compress' ] == 'on' ) ? TRUE : FALSE;

			$this->show_download_button( $report[ 'filename' ], $compress );

		}

	}

	/**
	 * @param void
	 *
	 * @return null
	 * calls the file delivery in Class DatabaseExporter
	 */
	public function deliver_backup_file() {

		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "download_file" ) {
			if ( isset ( $_POST[ 'sql_file' ] ) ) {
				$sql_file = $_POST[ 'sql_file' ];
			}

			if ( isset ( $_POST[ 'compress' ] ) ) {
				$compress = $_POST[ 'compress' ];
			}
			//TODO: Make this safer
			$this->dbe->deliver_backup( $sql_file, $compress );
		}

	}

	/**
	 * creates an input element to start the download of the sql file
	 *
	 * @param $file     The name of the file to be downloaded
	 * @param $compress Set true if gz compression should be used
	 */
	protected function show_download_button( $file, $compress ) {

		echo( '<div class="updated insr_sql_button_wrap">	<form action method="post">' );
		echo _e( 'Your SQL file was created!' );
		wp_nonce_field( 'download_sql', 'insr_nonce' );
		$value = translate( "Download SQL File", "insr" );

		$html = '<input type="hidden" name="action" value="download_file" /><input type ="hidden" name="sql_file" value="' . $file . '"><input type ="hidden" name="compress" value="' . $compress . '"><input id ="insr_submit"type="submit" value="' . $value . ' "class="button" /></form></div>';
		echo $html;

	}

	/**
	 *displays the html for the submit button
	 */
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
	 * @param $tables  The array of tables we want to search
	 * @param $dry_run True if dry run (no changes are written to db)
	 *
	 * @return null
	 */
	protected function run_replace( $search, $replace, $tables, $dry_run
	) {

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

		$report = $this->replace->run_search_replace( $search, $replace, $tables );
		foreach ( $report[ 'changes' ] as $table_report ) {
			$this->show_changes( $table_report );

		}

		//if no changes found report that
		if ( count( $report [ 'changes' ] ) == 0 ) {
			echo __( 'Search pattern not found.', 'insr' );
		}
		echo '</div>';

	}

	/**
	 * displays the changes made to the db
	 * echoes the changes in formatted html
	 *
	 *
	 * @param $results          Array  with at least these elements:
	 *                          'table_name'=>$[name of current table],
	 *                          'changes' => array('row'    => [row that has been changed ],
	 *                          'column' => [column that has been changed],
	 *                          'from'   => ( old value ),
	 *                          'to'     => ( $new value ),
	 *
	 * @return string
	 *
	 *
	 */

	protected function show_changes( $results ) {

		$changes      = $results[ 'changes' ];
		$changes_made = count( $changes );

		if ( $changes_made > 0 ) {
			$table = $results[ 'table_name' ];

			$html = '<table class = "widefat fixed"><thead><strong>' . __( 'Table', 'insr' ) . ':  </strong>' . $table;
			$html .= '&nbsp; <strong>  ' . __( 'Changes', 'insr' ) . ': </strong> ' . $changes_made . '<thead>';
			foreach ( $changes as $change ) {

				$html .= '<tr>';
				$html .= '<th>' . __( 'row', 'insr' ) . '</th>
						<td>' . $change [ 'row' ] . '</td>
				         <th> ' . __( 'column', 'insr' ) . '</th>
				        <td>' . $change [ 'column' ] . '</td> ';
				$html .= '<th>' . __( 'Old value:', 'insr' ) . '</th>
							<td>' . esc_html( $change [ 'from' ] ) . '</th><td>' . '</td>
						<th> ' . __( 'New value:', 'insr' ) . '</th><td>' . esc_html( $change[ 'to' ] ) . '</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';

			echo $html;
		}

	}

	/**
	 *checks the input form and writes possible errors to a WP_Error object
	 */
	protected function check_input_form() {

		if ( ! isset ( $_POST[ 'select_tables' ] ) ) {

			$this->errors->add( 'no_table_selected', __( 'No Tables were selected.', 'insr' ) );

		}
		if ( ! isset ( $_POST[ 'search' ] ) || $_POST[ 'search' ] == "" ) {
			$this->errors->add( 'empty_search', __( 'Search field is empty.', 'insr' ) );
		}
	}

	/**
	 * echoes the content of the $errors array as formatted HTML
	 *
	 *
	 */

	protected function display_errors() {

		echo '<div class = "error"><strong>' . __( 'Errors:', 'insr' ) . '</strong><ul>';
		$messages = $this->errors->get_error_messages();
		foreach ( $messages as $error ) {
			echo '<li>' . $error . '</li>';
		}
		echo '</ul></div>';
	}

}