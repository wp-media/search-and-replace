<?php

namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database;

/**
 * Class SqlImport
 *
 * @package Inpsyde\SearchReplace\inc\Page
 */
class SqlImport extends AbstractPage implements PageInterface {

	/**
	 * @var Database\Importer
	 */
	private $dbi;

	/**
	 * SqlImport constructor.
	 *
	 * @param Database\Importer $dbi
	 */
	public function __construct( Database\Importer $dbi ) {

		$this->dbi = $dbi;
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return esc_html__( 'SQL Import', 'search-and-replace' );
	}

	/**
	 * Return the static slug string.
	 *
	 * @return string
	 */
	public function get_slug() {

		return 'sql-import';
	}

	/**
	 * Callback function for menu item
	 */
	public function render() {

		require_once dirname(__DIR__) . '/templates/sql-import.php';
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Import SQL file', 'search-and-replace' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {

		// @ToDo: Better handling of large files
		// maybe like here: http://stackoverflow.com/questions/147821/loading-sql-files-from-within-php , answer by user 'gromo'
		$php_upload_error_code = $_FILES[ 'file_to_upload' ][ 'error' ];
		if ( 0 === $php_upload_error_code ) {
			// get file extension
			$ext = strrchr( $_FILES [ 'file_to_upload' ][ 'name' ], '.' );
			// parse file
			$tempfile = $_FILES [ 'file_to_upload' ][ 'tmp_name' ];
			switch ( $ext ) {
				case '.sql':
					// @codingStandardsIgnoreLine
					$sql_source = file_get_contents( $tempfile );
					break;
				case '.gz':
					$sql_source = $this->read_gzfile_into_string( $tempfile );
					break;
				default:
					$this->add_error(
						esc_html__(
							'The file has neither \'.gz\' nor \'.sql\' Extension. Import not possible.',
							'search-and-replace'
						)
					);
					return;
			}

			// call import function
			$success = $this->dbi->import_sql( $sql_source );
			if ( - 1 === $success ) {
				$this->add_error(
					esc_html__(
						'The file does not seem to be a valid SQL file. Import not possible.',
						'search-and-replace'
					)
				);
			} else {
				echo '<div class="updated notice is-dismissible">';
				echo '<p>';
				printf(
				// Translators: %s print the sql source.
					esc_html__(
						'The SQL file was successfully imported. %s SQL queries were performed.',
						'search-and-replace'
					),
					esc_html($success)
				);
				echo '</p></div>';
			}
		} else {
			// show error
			$php_upload_errors = array(
				0 => esc_html__(
					'There is no error, the file uploaded with success',
					'search-and-replace'
				),
				1 => esc_html__(
					'The uploaded file exceeds the upload_max_filesize directive in php.ini',
					'search-and-replace'
				),
				2 => esc_html__(
					'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
					'search-and-replace'
				),
				3 => esc_html__(
					'The uploaded file was only partially uploaded',
					'search-and-replace'
				),
				4 => esc_html__(
					'No file was uploaded.',
					'search-and-replace'
				),
				6 => esc_html__(
					'Missing a temporary folder.',
					'search-and-replace'
				),
				7 => esc_html__(
					'Failed to write file to disk.',
					'search-and-replace' ),
				8 => esc_html__(
					'A PHP extension stopped the file upload.',
					'search-and-replace'
				),
			);

			$this->add_error(
				sprintf(
					// Translators: %s print the error message.
					esc_html__( 'Upload Error: %s', 'search-and-replace' ),
					$php_upload_errors[ $php_upload_error_code ]
				)
			);
		}

	}

	/**
	 * Reads a gz file into a string.
	 *
	 * @param string $filename String path ot file.
	 *
	 * @return string The file contents.
	 */
	private function read_gzfile_into_string( $filename ) {

		$zd       = gzopen( $filename, 'r' );
		$contents = gzread( $zd, 10000 );
		gzclose( $zd );

		return $contents;
	}

	/**
	 * Returns a file size limit in kilobytes based on the PHP upload_max_filesize and post_max_size.
	 *
	 * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
	 *
	 * @return float
	 */
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

	/**
	 * @param int $size
	 *
	 * @return float
	 */
	private function parse_size( $size ) {

		// Remove the non-unit characters from the size.
		$unit = preg_replace( '/[^bkmgtpezy]/i', '', $size );
		// Remove the non-numeric characters from the size.
		$size = preg_replace( '/[^0-9\.]/', '', $size );
		if ( $unit ) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round( $size * ( 1024 ** stripos( 'bkmgtpezy', $unit[0] ) ) );
		}
		return round( $size );
	}

}
