<?php

namespace Inpsyde\SearchAndReplace\Page;

use Inpsyde\SearchAndReplace\Database;
use Inpsyde\SearchAndReplace\FileDownloader;
use Inpsyde\SearchAndReplace\Settings\AbstractPage;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * Class BackupDatabase
 *
 * @package Inpsyde\SearchAndReplace\Page
 */
class BackupSettingsPage extends AbstractPage implements SettingsPageInterface {

	/**
	 * @var Database\Exporter
	 */
	private $dbe;

	/**
	 * @var FileDownloader
	 */
	private $downloader;

	/**
	 * BackupDatabase constructor.
	 *
	 * @param Database\Exporter $dbe
	 * @param FileDownloader    $downloader
	 */
	public function __construct( Database\Exporter $dbe, FileDownloader $downloader ) {

		$this->dbe        = $dbe;
		$this->downloader = $downloader;
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return esc_html__( 'Backup Database', 'search-and-replace' );
	}

	/**
	 * Return the static slug string.
	 *
	 * @return string
	 */
	public function get_slug() {

		return 'backup-database';
	}

	/**
	 * Shows the page template
	 */
	public function render() {

		?>
		<p>
			<?php esc_html_e(
				'Create a backup of your database by clicking "Create SQL File".',
				'search-and-replace'
			); ?>
		</p>
		<form action="" method="post">
			<?php $this->show_submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Create SQL File', 'search-and-replace' );
	}

	/**
	 * Event handler for click on export sql button.
	 *
	 * @param array $request_data
	 */
	public function save( array $request_data = [] ) {

		$report = $this->dbe->db_backup();
		$this->downloader->show_modal( $report );

		return TRUE;
	}

}
