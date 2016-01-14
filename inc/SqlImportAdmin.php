<?php
/**
 *
 */

namespace Inpsyde\SearchReplace\inc;

class SqlImportAdmin extends Admin {

	/**
	 *callback function for menu item
	 */
	public function show_page() {

		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "sql_import" ) {
			$this->handle_sql_import_event();

		}
		$this->display_errors();
		require_once( 'templates/sql_import.php' );
	}

	/**
	 *displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'sql_import', 'insr_nonce' );

		$html = '	<input type="hidden" name="action" value="sql_import" />';
		echo $html;
		submit_button( __( 'Import SQL file', 'insr' ) );

	}

	/**
	 *starts the sql import
	 *
	 * @return void
	 */
	private function handle_sql_import_event() {

		//TODO: Better handling of large files, maybe like here: http://stackoverflow.com/questions/147821/loading-sql-files-from-within-php , answer 3

		if ( $_FILES [ 'file_to_upload' ][ 'error' ] == 0 ) {

			//get file extension
			$ext = strrchr( $_FILES [ 'file_to_upload' ][ 'name' ], '.' );
			//parse file
			$tempfile = $_FILES [ 'file_to_upload' ][ 'tmp_name' ];
			switch ( $ext ) {
				case '.sql':
					$sql_source = file_get_contents( $tempfile );
					break;
				case '.gz':
					$sql_source = $this->read_gzfile_into_string( $tempfile );
					break;
				default:
					$this->errors->add( 'sql_import_error', __( 'The file has neither \'.gz\' nor \'.sql\' Extension.  Import not possible.', 'insr' ) );

					return;
			}

			//call import function
			$success = $this->dbm->import_sql( $sql_source, $this->errors );
			if ( $success == - 1 ) {
				$this->errors->add( 'sql_import_error', __( 'The file does not seem to be a valid SQL file. Import not possible.', 'insr' ) );
			} else {
				echo '<div class = "updated notice is-dismissible">';
				echo '<p>';
				printf( __( 'The SQL file was successfully imported. %s SQL queries were performed.', 'insr' ), $success );
				echo '</p></div>';
			}
		} else {
			//show error
			$this->errors->add( 'upload_error', __( 'Upload Error. Error Code: ' . $_FILES[ 'file_to_upload' ][ 'error' ], 'insr' ) );
		}

	}

	/**
	 * reads a gz file into a string
	 * @param $filename String path ot file
	 *
	 * @return string The file contents
	 */
	private function read_gzfile_into_string( $filename ) {

		$zd       = gzopen( $filename, "rb" );
		$contents = gzread( $zd, 10000 );
		gzclose( $zd );

		return $contents;
	}

}