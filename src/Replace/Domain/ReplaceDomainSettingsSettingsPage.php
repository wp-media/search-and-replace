<?php

namespace Inpsyde\SearchAndReplace\Replace\Domain;

use Brain\Nonces\NonceInterface;
use Inpsyde\SearchAndReplace\Database\DatabaseBackup;
use Inpsyde\SearchAndReplace\Database\Manager;
use Inpsyde\SearchAndReplace\File\FileDownloader;
use Inpsyde\SearchAndReplace\Settings\AbstractSettingsPage;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Replace\Domain
 */
class ReplaceDomainSettingsSettingsPage extends AbstractSettingsPage implements SettingsPageInterface {

	/**
	 * @var \Inpsyde\SearchAndReplace\Database\DatabaseBackup
	 */
	private $exporter;

	/**
	 * @var SettingsManager
	 */
	private $db_manager;

	/**
	 * @var FileDownloader
	 */
	private $downloader;

	/**
	 * ReplaceDomain constructor.
	 *
	 * @param Manager        $manager
	 * @param DatabaseBackup $exporter
	 * @param FileDownloader $downloader
	 */
	public function __construct( Manager $manager, DatabaseBackup $exporter, FileDownloader $downloader ) {

		$this->db_manager = $manager;
		$this->exporter   = $exporter;
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
			? filter_var( $_POST[ 'change_db_prefix' ], FILTER_VALIDATE_BOOLEAN )
			: FALSE;
		// @codingStandardsIgnoreEnd

		$new_db_prefix = $change_db_prefix ? $new_db_prefix : '';

		// Make the backup.
		$report = $this->exporter->backup( $search, $replace, [], TRUE, $new_db_prefix );

		// Show the replace report.
		$this->downloader->show_modal( $report );

		return TRUE;
	}

	/**
	 * Shows the page template
	 */
	public function render( NonceInterface $nonce ) {

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
						<?php echo esc_html( $this->db_manager->get_base_prefix() ); ?>
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
			<?= \Brain\Nonces\formField( $nonce ) /* xss ok */ ?>
			<?php $this->show_submit_button(); ?>
		</form>

		<?php
	}

}
