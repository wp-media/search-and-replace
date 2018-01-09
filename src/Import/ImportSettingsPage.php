<?php

namespace Inpsyde\SearchAndReplace\Import;

use Inpsyde\SearchAndReplace\Database\Importer;
use Inpsyde\SearchAndReplace\File\UploadedFile;
use Inpsyde\SearchAndReplace\Settings\AbstractPage;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Import
 */
class ImportSettingsPage extends AbstractPage implements SettingsPageInterface {

	/**
	 * @var string
	 */
	const FILE_KEY = 'search-and-replace__file';

	/**
	 * @var array
	 */
	private $allowed_extensions = [ 'gz', 'sql' ];

	/**
	 * @var Importer
	 */
	private $importer;

	/**
	 * ImportSettingsPage constructor.
	 *
	 * @param Importer $importer
	 */
	public function __construct( Importer $importer ) {

		$this->importer = $importer;
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

					<td>
						<input
							type="file"
							name="<?= esc_attr( self::FILE_KEY ) ?>"
							id="<?= esc_attr( self::FILE_KEY ) ?>"
						/>
						<p class="form-description description">#
							<?php
							printf(
								__( 'Allowed file extensions are: "%s"', 'search-and-replace' ),
								implode( ', "', $this->allowed_extensions )
							);
							?>
						</p>
					</td>
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

		$file = new UploadedFile( $_FILES[ self::FILE_KEY ] );

		if ( $file->error() !== UPLOAD_ERR_OK ) {

			$this->add_error(
				sprintf(
					esc_html__( 'Upload Error: %s', 'search-and-replace' ),
					$file->error_message()
				)
			);

			return FALSE;
		}

		if ( ! in_array( $ext, $this->allowed_extensions, TRUE ) ) {
			$this->add_error(
				esc_html__(
					'The file has neither \'.gz\' nor \'.sql\' Extension. Import not possible.',
					'search-and-replace'
				)
			);

			return FALSE;
		}

		$sql = '';
		$ext = $file->getExtension();

		if ( $ext === 'sql' ) {
			// @codingStandardsIgnoreLine
			$sql = file_get_contents( $file->name() );
		} elseif ( $ext === 'gz' ) {
			$zd  = gzopen( $file->name(), 'r' );
			$sql = gzread( $zd, 10000 );
			gzclose( $zd );
		}

		if ( $sql === '' ) {
			$this->add_error(
				esc_html__(
					'The given file is empty or loading file content was not possible',
					'search-and-replace'
				)
			);

			return FALSE;
		}

		// call import function
		$success = $this->importer->import( $sql );
		if ( - 1 === $success ) {
			$this->add_error(
				esc_html__(
					'The file does not seem to be a valid SQL file. Import not possible.',
					'search-and-replace'
				)
			);

			return FALSE;
		}

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
