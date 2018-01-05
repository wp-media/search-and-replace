<?php

namespace Inpsyde\SearchAndReplace\Import;

use Inpsyde\SearchAndReplace\Database;
use Inpsyde\SearchAndReplace\Settings\AbstractPage;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Import
 */
class ImportSettingsPage extends AbstractPage implements SettingsPageInterface {

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
	 * {@inheritdoc}
	 */
	public function get_page_title() {

		return esc_html__( 'SQL Import', 'search-and-replace' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_slug() {

		return 'sql-import';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Import SQL file', 'search-and-replace' );
	}

	/**
	 * Callback function for menu item
	 */
	public function render() {

		?>

		<form action="" method="post" enctype="multipart/form-data">
			<table class="form-table">
				<tbody>
				<tr>
					<th>
						<strong>
							<?php esc_html_e( 'Select SQL file to upload. ', 'search-and-replace' ); ?>
						</strong>
					</th>

					<td><input type="file" name="file_to_upload" id="file_to_upload"></td>
				</tr>
				<tr>
					<th></th>
					<td>
						<?php esc_html_e( 'Maximum file size: ', 'search-and-replace' ); ?>
						<?php echo floatval( $this->file_upload_max_size() ) . 'KB'; ?>
					</td>
				</tr>
				</tbody>
			</table>
			<?php $this->show_submit_button(); ?>
		</form>

		<?php
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( array $request_data = [] ) {

		// TODO: Better handling of large files
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

					return FALSE;
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
					esc_html__(
						'The SQL file was successfully imported. %s SQL queries were performed.',
						'search-and-replace'
					),
					$success
				);
				echo '</p></div>';

				return TRUE;
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
					'search-and-replace'
				),
				8 => esc_html__(
					'A PHP extension stopped the file upload.',
					'search-and-replace'
				),
			);

			$this->add_error(
				sprintf(
					esc_html__( 'Upload Error: %s', 'search-and-replace' ),
					$php_upload_errors[ $php_upload_error_code ]
				)
			);
		}

		return FALSE;
	}

	/**
	 * reads a gz file into a string
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
			return round( $size * pow( 1024, stripos( 'bkmgtpezy', $unit[ 0 ] ) ) );
		} else {
			return round( $size );
		}
	}

}
