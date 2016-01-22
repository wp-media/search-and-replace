<?php

namespace Inpsyde\SearchReplace\inc;

class Admin {

	/**
	 * @var \WP_Error
	 */
	protected $errors;

	public function __construct() {

		$this->dbm     = new DatabaseManager();
		$this->replace = new Replace( $this->dbm );
		$this->dbe     = new DatabaseExporter( $this->replace, $this->dbm );
		$this->errors  = new \WP_Error();

		//if "download" was selected we have to check that early to prevent "headers already sent" error
		$this->add_file_download_action();

	}

	/**
	 *checks input, creates a sql backup file, shows changes and download button
	 *
	 * @param void
	 *
	 * @return null
	 */
	protected function create_backup_file( $search, $replace, $tables, $domain_replace = FALSE ) {

		$report = $this->dbe->db_backup( $search, $replace, $tables, $domain_replace );
		if ( $search != '' ) {
			echo '<div class = "updated notice is-dismissible">';
			//show changes if there are any
			if ( count( $report[ 'changes' ] ) > 0 ) {
				$this->show_changes( $report );
			}

			//if no changes found report that
			if ( count( $report [ 'changes' ] ) == 0 ) {
				echo '<p>' . __( 'Search pattern not found.', 'insr' ) . '</p>';
			}

			echo '</div>';
		}

		$compress = ( isset ( $_POST[ 'compress' ] ) && $_POST [ 'compress' ] == 'on' ) ? TRUE : FALSE;

		$this->show_download_button( $report[ 'filename' ], $compress );

	}

	/**
	 * displays the changes made to the db
	 * echoes the changes in formatted html
	 *
	 *
	 * @param $report           array 'errors' : WP-Error Object if Errors
	 *
	 * 'tables' : Number of tables processed
	 * 'changes_count' : Number of changes made
	 *                                'changes'
	 *                          Array  with at least these elements:
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

	protected function show_changes( $report ) {

		//get search & replace values in order to highlight them in the results
		$search            = $_POST [ 'search' ];
		$search_highlight  = '<span class="search-replace-search-value">' . $search . '</span>';
		$replace           = $_POST [ 'replace' ];
		$replace_highlight = '<span class ="search-replace-replace-value">' . $replace . '</span>';
		$delimiter         = array( " ...", "...<br> " );

		$msg = sprintf( __( '<p><strong>%d</strong> tables were processed, <strong>%d</strong> cells were found that need to be updated.</p>', 'insr' ),
		                $report[ 'tables' ],
		                $report[ 'changes_count' ] );

		echo $msg;

		//create modal window for detailed view of changes
		?>

		<a href="#" id="changes-modal-button"><?php _e( 'View details', 'insr' ); ?></a>
		<div id="changes-modal-background" class="search-replace-modal-background" style="display: none;"></div>
		<div id="changes-modal" class="search-replace-modal " style="display: none;">
			<div class="search-replace-modal-header">
				<button type="button" id="changes-modal-close" class="notice-dismiss"></button>
			</div>
			<div class="search-replace-changes-modal-content">
		<?php
		foreach ( $report[ 'changes' ] as $table_report ) {
			$changes      = $table_report[ 'changes' ];
			$changes_made = count( $changes );

			if ( $changes_made > 0 ) {
				$table = $table_report[ 'table_name' ];
				$html  = '<h2 class = "search-replace-modal-table-headline"><strong>' . __( 'Table', 'insr' ) . ': </strong>' . $table . ' <strong>' . __( 'Changes',
				                                                                                                                                           'insr' ) . ': </strong> ' . $changes_made . '</h2>';

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
	 * @param $file     String The name of the file to be downloaded
	 * @param $compress Boolean Set true if gz compression should be used
	 */
	protected function show_download_button( $file, $compress ) {

		echo( '<div class="updated notice is-dismissible insr_sql_button_wrap">	<p>' );
		echo __( 'Your SQL file was created!' );
		echo( '</p><form action method="post">' );
		wp_nonce_field( 'download_sql', 'insr_nonce' );
		$value = translate( "Download SQL File", "insr" );

		$html = '<input type="hidden" name="action" value="download_file" /><input type ="hidden" name="sql_file" value="' . $file . '"><input type ="hidden" name="compress" value="' . $compress . '"><input id ="insr_submit"type="submit" value="' . $value . ' "class="button" /></form></div>';
		echo $html;

	}

	/**
	 * echoes the content of the $errors array as formatted HTML if it contains error messages
	 *
	 */
	protected function display_errors() {

		$messages = $this->errors->get_error_messages();
		if ( count( $messages ) > 0 ) {

			echo '<div class = "error notice is-dismissible"><strong>' . __( 'Errors:', 'insr' ) . '</strong><ul>';

			foreach ( $messages as $error ) {
				echo '<li>' . $error . '</li>';
			}
			echo '</ul></div>';
		}
	}

	/**
	 *adds the action to "deliver backup file" on "init" to prevent "header already sent" error
	 */
	private function add_file_download_action() {

		add_action( 'init', array( $this, 'deliver_backup_file' ) );
	}

	/**
	 *returns the site url, strips http:// or https://
	 */
	protected function get_stripped_site_url() {

		$url          = get_site_url();
		$stripped_url = substr( $url, strpos( $url, '/' ) + 2 );

		return $stripped_url;

	}

	/**
	 * trims a given string to 50 chars before and after the search string, if the string is longer than 199 chars
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
		//get all ocurrences of $needle with up to 50 chars front & back
		preg_match_all( "@.{0,50}" . $needle . ".{0,50}@", $haystack, $trimmed_results );
		$return_value = "";
		for ( $i = 0; $i < count( $trimmed_results ); $i ++ ) {
			//reset delimiter, might have been changed
			$local_delimiter = $delimiter;
			//check if the first trimmmed result is the beginning of $haystack. if so remove leading delimiter
			if ( $i == 0 ) {
				$pos = strpos( $haystack, $trimmed_results[ 0 ][ $i ] );
				if ( $pos === 0 ) {
					$local_delimiter[ 0 ] = "";
				}
			}

			//check if the last trimmed result is the end of $haystack. if so, remove trailing delimiter
			$last_index = count( $trimmed_results ) - 1;
			if ( $i == $last_index ) {
				$trimmed_result_length = strlen( $trimmed_results[ 0 ][ $i ] );
				$substring             = substr( $haystack, - $trimmed_result_length );
				if ( $substring == $trimmed_results[ 0 ][ $i ] ) {
					$local_delimiter[ 1 ] = "";
				}

			}
			$return_value .= $local_delimiter[ 0 ] . $trimmed_results[ 0 ][ $i ] . $local_delimiter[ 1 ];
		}

		return $return_value;
	}

}