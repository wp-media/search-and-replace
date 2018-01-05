<?php

namespace Inpsyde\SearchAndReplace\Replace\Domain;

use Inpsyde\SearchAndReplace\Database;
use Inpsyde\SearchAndReplace\FileDownloader;
use Inpsyde\SearchAndReplace\Settings\AbstractPage;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Replace\Domain
 */
class ReplaceDomainSettingsPage extends AbstractPage implements SettingsPageInterface {

	/**
	 * @var \Inpsyde\SearchAndReplace\Database\Exporter
	 */
	private $dbe;

	/**
	 * @var SettingsManager
	 */
	private $dbm;

	/**
	 * @var FileDownloader
	 */
	private $downloader;

	/**
	 * ReplaceDomain constructor.
	 *
	 * @param Database\Manager  $dbm
	 * @param Database\Exporter $dbe
	 * @param FileDownloader    $downloader
	 */
	public function __construct( Database\Manager $dbm, Database\Exporter $dbe, FileDownloader $downloader ) {

		$this->dbm        = $dbm;
		$this->dbe        = $dbe;
		$this->downloader = $downloader;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Do Replace Domain/Url', 'search-and-replace' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {

		return esc_html__( 'Replace Domain URL', 'search-and-replace' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_slug() {

		return 'replace-domain-url';
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( array $request_data = [] ) {

		$search        = esc_url_raw( $request_data[ 'search' ] );
		$replace       = esc_url_raw( $request_data[ 'replace' ] );
		$new_db_prefix = esc_attr( $request_data[ 'new_db_prefix' ] );

		// search field should not be empty
		if ( '' === $replace ) {
			$this->add_error( esc_html__( 'Replace Field should not be empty.', 'search-and-replace' ) );

			return FALSE;
		}

		// Do not pass the new db prefix if `change_db_prefix` isn't flagged.
		// @codingStandardsIgnoreStart
		$change_db_prefix = isset( $_POST[ 'change_db_prefix' ] )
			?
			filter_var( $_POST[ 'change_db_prefix' ], FILTER_VALIDATE_BOOLEAN )
			:
			FALSE;
		// @codingStandardsIgnoreEnd

		$new_db_prefix = $change_db_prefix ? $new_db_prefix : '';

		// Make the backup.
		$report = $this->dbe->db_backup( $search, $replace, [], TRUE, $new_db_prefix );

		// Show the replace report.
		$this->downloader->show_modal( $report );

		return TRUE;
	}

	/**
	 * Shows the page template
	 */
	public function render() {

		?>

		<form action="" method="post">

			<table class="form-table">
				<tbody>

				<tr>
					<th>
						<label for="search">
							<?php esc_html_e( 'Search for: ', 'search-and-replace' ); ?>
						</label>
					</th>
					<td>
						<input id="search" type="text" name="search" value="<?php esc_url( get_site_url() ); ?>" />
					</td>
				</tr>

				<tr>
					<th>
						<label for="replace">
							<?php esc_html_e( 'Replace with: ', 'search-and-replace' ); ?>
						</label>
					</th>
					<td>
						<input
							id="replace"
							type="text"
							name="replace"
							placeholder="<?php esc_attr_e( 'New URL', 'search-and-replace' ); ?>"
						/>
					</td>
				</tr>

				<tr>
					<th>
						<label for="change_db_prefix">
							<?php esc_html_e( 'Change database prefix', 'search-and-replace' ); ?>
						</label>
					</th>
					<td><input id="change_db_prefix" type="checkbox" name="change_db_prefix" /></td>
				</tr>

				<tr class="disabled">
					<th>
						<label for="current_db_prefix">
							<?php esc_html_e( 'Current prefix: ', 'search-and-replace' ); ?>
						</label>
					</th>
					<td>
						<?php echo esc_html( $this->dbm->get_base_prefix() ); ?>
					</td>
				</tr>

				<tr class="maybe_disabled disabled">
					<th>
						<label for="new_db_prefix">
							<?php esc_html_e( 'New prefix: ', 'search-and-replace' ); ?>
						</label>
					</th>
					<td>
						<input
							id="new_db_prefix"
							type="text"
							name="new_db_prefix"
							disabled
							placeholder="<?php esc_attr_e( 'E.g new_', 'search-and-replace' ); ?>"
						/>
						<p>
							<?php esc_html_e( 'Underscore suffix "_" can be omitted', 'search-and-replace' ); ?>
						</p>
					</td>
				</tr>
				</tbody>
			</table>
			<?php $this->show_submit_button(); ?>
		</form>

		<?php
	}

}
