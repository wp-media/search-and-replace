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
	 * @var DatabaseImporter
	 */
	protected $dbi;

	/**
	 * @var Replace
	 */
	protected $replace;

	/**
	 * @var \WP_Error
	 */
	protected $errors;

	/**
	 * Admin constructor.
	 */
	public function __construct() {

		$this->dbm     = new DatabaseManager();
		$this->replace = new Replace( $this->dbm );
		$this->dbe     = new DatabaseExporter( $this->replace, $this->dbm );
		$this->dbi     = new DatabaseImporter();
		$this->errors  = new \WP_Error();

		//if "download" was selected we have to check that early to prevent "headers already sent" error
		$this->add_file_download_action();

	}

	/**
	 * Checks input, creates a sql backup file, shows changes and download button.
	 *
	 * @param        $search
	 * @param        $replace
	 * @param        $tables
	 * @param bool   $domain_replace
	 * @param string $new_table_prefix
	 */
	protected function create_backup_file( $search, $replace, $tables, $domain_replace = FALSE, $new_table_prefix = '' ) {

		$report = $this->dbe->db_backup( $search, $replace, $tables, $domain_replace, $new_table_prefix );
		if ( $search !== '' && $search !== $replace ) {
			echo '<div class="updated notice is-dismissible">';
			//show changes if there are any
			if ( count( $report[ 'changes' ] ) > 0 ) {
				$this->show_changes( $report );
			}

			//if no changes found report that
			if ( 0 === count( $report [ 'changes' ] ) ) {
				echo '<p>' . esc_html__( 'Search pattern not found.', 'insr' ) . '</p>';
			}

			echo '</div>';
		}

		$compress = (bool) ( isset( $_POST[ 'compress' ] ) && 'on' === $_POST[ 'compress' ] );

		$this->show_download_button( $report[ 'filename' ], $compress );

	}

	/**
	 * displays the changes made to the db
	 * echoes the changes in formatted html
	 *
	 * @param $report                 array 'errors' : WP-Error Object if Errors
	 *                                'tables' : Number of tables processed
	 *                                'changes_count' : Number of changes made
	 *                                'changes'
	 *                                Array  with at least these elements:
	 *                                'table_name'=>$[name of current table],
	 *                                'changes' => array('row'    => [row that has been changed ],
	 *                                'column' => [column that has been changed],
	 *                                'from'   => ( old value ),
	 *                                'to'     => ( $new value ),
	 *
	 * @return string
	 */

	protected function show_changes( $report ) {

		//get search & replace values in order to highlight them in the results
		$search            = esc_html( $_POST [ 'search' ] );
		$search_highlight  = '<span class="search-replace-search-value">' . $search . '</span>';
		$replace           = esc_html( $_POST [ 'replace' ] );
		$replace_highlight = '<span class ="search-replace-replace-value">' . $replace . '</span>';
		$delimiter         = array( ' ...', '...<br>' );

		$msg = sprintf(
			_n(
				'%s table was processed.',
				'%s tables were processed.',
				$report[ 'tables' ],
				'insr'
			),
			$report[ 'tables' ]
		);

		$msg .= sprintf(
			_n(
				'%s cell needs to be updated.',
				'%s cells need to be updated.',
				$report[ 'changes_count' ],
				'insr'
			),
			$report[ 'changes_count' ]
		);
		echo esc_html( $msg );

		//create modal window for detailed view of changes
		?>
		<p><a href="#" id="changes-modal-button"><?php esc_html_e( 'View details', 'insr' ); ?></a></p>
		<div id="changes-modal-background" class="search-replace-modal-background" style="display: none;"></div>
		<div id="changes-modal" class="search-replace-modal " style="display: none;">
			<div class="search-replace-modal-header">
				<button type="button" id="changes-modal-close" class="search-replace-modal-close-button"></button>
			</div>
			<div class="search-replace-changes-modal-content">
		<?php
		foreach ( $report[ 'changes' ] as $table_report ) {
			$changes      = $table_report[ 'changes' ];
			$changes_made = count( $changes );

			if ( $changes_made > 0 ) {
				$table = $table_report[ 'table_name' ];
				$html  = '<h2 class = "search-replace-modal-table-headline">';
				$html .= '<strong>' . esc_attr__( 'Table:', 'insr' ) . '</strong> ' . $table;
				$html .= '<strong>' . esc_attr__( 'Changes:', 'insr' ) . '</strong> ' . $changes_made;
				$html .= '</h2>';

				$html .= '<table class="search-replace-modal-table"><colgroup><col><col><col><col><col><col><col><col></colgroup>';

				foreach ( $changes as $change ) {

					$html .= '<tr>';
					$html .= '<th class="search-replace-narrow">' . __( 'row', 'insr' ) . '</th>
						<td class="search-replace-narrow">' . $change [ 'row' ] . '</td>
				         <th> ' . __( 'column', 'insr' ) . '</th>
				        <td>' . $change [ 'column' ] . '</td> ';

					//trim results and wrap with highlight class
					$old_value = esc_html( $change [ 'from' ] );
					$old_value = $this->trim_search_results( $search, $old_value, $delimiter );
					$old_value = str_replace( $search, $search_highlight, $old_value );

					$new_value = esc_html( $change[ 'to' ] );
					$new_value = $this->trim_search_results( $replace, $new_value, $delimiter );
					$new_value = str_replace( $replace, $replace_highlight, $new_value );

					$html .= '<th>' . __( 'Old value:', 'insr' ) . '</th>
							<td>' . $old_value . '</td>
						<th> ' . __( 'New value:', 'insr' ) . '</th><td>' . $new_value . '</td>';
					$html .= '</tr>';
				}
				$html .= '</table>';

				echo $html;
			}
		}

		echo '</div></div>';
	}

	/**
	 * @param void
	 *
	 * @return null
	 * calls the file delivery in Class DatabaseExporter
	 */
	public function deliver_backup_file() {

		if ( isset( $_POST[ 'action' ] ) && 'download_file' === $_POST[ 'action' ] ) {

			$sql_file = '';
			if ( isset( $_POST[ 'sql_file' ] ) ) {
				$sql_file = $_POST[ 'sql_file' ];
			}

			$compress = FALSE;
			if ( isset( $_POST[ 'compress' ] ) ) {
				$compress = $_POST[ 'compress' ];
			}

			// If file name contains path or does not end with '.sql' exit.
			$ext = strrchr( $sql_file, '.' );
			if ( FALSE !== strpos( $sql_file, '/' ) || '.sql' !== $ext ) {
				die;
			}
			$this->dbe->deliver_backup( $sql_file, $compress );
		}

	}

	/**
	 * creates an input element to start the download of the sql file
	 *
	 * @param $file     String The name of the file to be downloaded
	 * @param $compress Boolean Set true if gz compression should be used
	 */
	protected function show_download_button( $file, $compress ) {

		echo '<div class="updated notice is-dismissible insr_sql_button_wrap"><p>';
		esc_attr_e( 'Your SQL file was created!', 'insr' );
		echo '</p><form action method="post">';
		wp_nonce_field( 'download_sql', 'insr_nonce' );
		$value = translate( 'Download SQL File', 'insr' );

		$html = '<input type="hidden" name="action" value="download_file" />';
		$html .= '<input type ="hidden" name="sql_file" value="' . esc_attr( $file ) . '">';
		$html .= '<input type ="hidden" name="compress" value="' . esc_attr( $compress ) . '">';
		$html .= '<input id ="insr_submit" type="submit" value="' . esc_attr( $value ) . '" class="button" />';
		$html .= '</form></div>';
		echo $html;
	}

	/**
	 * Echoes the content of the $errors array as formatted HTML if it contains error messages.
	 */
	protected function display_errors() {

		$messages = $this->errors->get_error_messages();
		if ( count( $messages ) > 0 ) {

			echo '<div class="error notice is-dismissible"><strong>' . esc_html__( 'Errors:',
			                                                                       'insr' ) . '</strong><ul>';

			foreach ( $messages as $error ) {
				echo '<li>' . esc_html( $error ) . '</li>';
			}
			echo '</ul></div>';
		}
	}

	/**
	 * Adds the action to "deliver backup file" on "init" to prevent "header already sent" error.
	 */
	private function add_file_download_action() {

		add_action( 'init', array( $this, 'deliver_backup_file' ) );
	}

	/**
	 * Returns the site url, strips http:// or https://
	 */
	protected function get_stripped_site_url() {

		$url = get_site_url();

		return substr( $url, strpos( $url, '/' ) + 2 );
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