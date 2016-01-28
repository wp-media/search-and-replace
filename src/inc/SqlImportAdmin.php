<?php

namespace Inpsyde\SearchReplace\inc;

class SqlImportAdmin extends Admin {

	/**
	 * SqlImportAdmin constructor.
	 */
	public function __construct() {

		$this->dbi    = new DatabaseImporter();
		$this->errors = new \WP_Error();
		parent::__construct();
	}

	/**
	 *callback function for menu item
	 */
	public function show_page() {

		if ( isset( $_POST[ 'action' ] ) && 'sql_import' === $_POST[ 'action' ]
		     && check_admin_referer( 'sql_import', 'insr_nonce' )
		) {
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
		submit_button( esc_html__( 'Import SQL file', 'insr' ) );
	}

	/**
	 *starts the sql import
	 *
	 * @return void
	 */
	private function handle_sql_import_event() {

		// TODO: Better handling of large files
		// maybe like here: http://stackoverflow.com/questions/147821/loading-sql-files-from-within-php , answer by user 'gromo'
		$php_upload_error_code = $_FILES[ 'file_to_upload' ][ 'error' ];
		if ( 0 === $php_upload_error_code ) {

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
					$this->errors->add(
						'sql_import_error',
						esc_html__(
							'The file has neither \'.gz\' nor \'.sql\' Extension.  Import not possible.',
							'insr'
						)
					);

					return;
			}

			//call import function
			$success = $this->dbi->import_sql( $sql_source, $this->errors );
			if ( - 1 === $success ) {
				$this->errors->add(
					'sql_import_error',
					esc_html__(
						'The file does not seem to be a valid SQL file. Import not possible.',
						'insr'
					)
				);
			} else {
				echo '<div class="updated notice is-dismissible">';
				echo '<p>';
				$msg = printf(
					__( 'The SQL file was successfully imported. %s SQL queries were performed.', 'insr' ),
					$success );
				echo esc_html( $msg );
				echo '</p></div>';
			}
		} else {
			//show error
			$php_upload_errors = array(
				0 => 'There is no error, the file uploaded with success',
				1 => esc_html__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'insr' ),
				2 => esc_html__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
				                 'insr' ),
				3 => esc_html__( 'The uploaded file was only partially uploaded', 'insr' ),
				4 => esc_html__( 'No file was uploaded.', 'insr' ),
				6 => esc_html__( 'Missing a temporary folder.', 'insr' ),
				7 => esc_html__( 'Failed to write file to disk.', 'insr' ),
				8 => esc_html__( 'A PHP extension stopped the file upload.', 'insr' ),
			);

			$this->errors->add(
				'upload_error',
				__( 'Upload Error: ' . $php_upload_errors[ $php_upload_error_code ], 'insr' )
			);
		}

	}

	/**
	 * reads a gz file into a string
	 *
	 * @param $filename String path ot file
	 *
	 * @return string The file contents
	 */
	private function read_gzfile_into_string( $filename ) {

		$zd       = gzopen( $filename, 'r' );
		$contents = gzread( $zd, 10000 );
		gzclose( $zd );

		return $contents;
	}

	// Returns a file size limit in kilobytes based on the PHP upload_max_filesize
	// and post_max_size
	//http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
	public function file_upload_max_size() {

		$max_size = - 1;

		if ( $max_size < 0 ) {
			// Start with post_max_size.
			$max_size = $this->parse_size( ini_get( 'post_max_size' ) );

			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$upload_max = $this->parse_size( ini_get( 'upload_max_filesize' ) );
			if ( $upload_max > 0 && $upload_max < $max_size ) {
				$max_size = $upload_max;
			}
		}

		return $max_size / 1024;
	}

	private function parse_size( $size ) {

		$unit = preg_replace( '/[^bkmgtpezy]/i', '', $size ); // Remove the non-unit characters from the size.
		$size = preg_replace( '/[^0-9\.]/', '', $size ); // Remove the non-numeric characters from the size.
		if ( $unit ) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round( $size * pow( 1024, stripos( 'bkmgtpezy', $unit[ 0 ] ) ) );
		} else {
			return round( $size );
		}
	}

}