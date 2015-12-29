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
		add_action( 'init', array( $this, 'deliver_backup_file' ) );

	}

	/**
	 *checks input, creates a sql backup file, shows changes and download button
	 *
	 * @param void
	 *
	 * @return null
	 */
	protected function create_backup_file( $search, $replace, $tables ) {

		$report = $this->dbe->db_backup( $search, $replace, $tables );
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

		//TODO: error handling

		$compress = ( isset ( $_POST[ 'compress' ] ) && $_POST [ 'compress' ] == 'on' ) ? TRUE : FALSE;

		$this->show_download_button( $report[ 'filename' ], $compress );

	}

	/**
	 * displays the changes made to the db
	 * echoes the changes in formatted html
	 *
	 *
	 * @param $report           Array 'errors' : WP-Error Object if Errors

								'tables' : Number of tables processed
								'changes_count' : Number of changes made
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
		$msg = sprintf( __( '<p><strong>%d</strong> tables were processed, <strong>%d</strong> cells were found that need to be updated.</p>', 'insr' ),
		                $report['tables'],
		                $report['changes_count']);

		echo $msg;
		//create modal window for detailed view of changes
		add_thickbox(); ?>
		<a href="#TB_inline?width=1000&height=550&inlineId=changes-modal" class="thickbox"><?php _e( 'View details', 'insr' ); ?></a>    <?php

		echo '<div id = "changes-modal" style= "display:none">';

		foreach ( $report[ 'changes' ] as $table_report ) {
			$changes      = $table_report[ 'changes' ];
			$changes_made = count( $changes );

			if ( $changes_made > 0 ) {
				$table = $table_report[ 'table_name' ];

				$html = '<table class="search-replace-modal-table"><tr><th colspan="8"><strong>' . __( 'Table', 'insr' ) . ': </strong>' . $table;
				$html .= '&nbsp;  <strong> ' . __( 'Changes', 'insr' ) . ': </strong>' . $changes_made . '</th></tr>';
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
		//close thickbox div
		echo '</div>';
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
		echo _e( 'Your SQL file was created!' );
		echo( '</p><form action method="post">' );
		wp_nonce_field( 'download_sql', 'insr_nonce' );
		$value = translate( "Download SQL File", "insr" );

		$html = '<input type="hidden" name="action" value="download_file" /><input type ="hidden" name="sql_file" value="' . $file . '"><input type ="hidden" name="compress" value="' . $compress . '"><input id ="insr_submit"type="submit" value="' . $value . ' "class="button" /></form></div>';
		echo $html;

	}

	/**
	 * echoes the content of the $errors array as formatted HTML
	 *
	 */
	protected function display_errors() {

		echo '<div class = "error notice is-dismissible"><strong>' . __( 'Errors:', 'insr' ) . '</strong><ul>';
		$messages = $this->errors->get_error_messages();
		foreach ( $messages as $error ) {
			echo '<li>' . $error . '</li>';
		}
		echo '</ul></div>';
	}

}