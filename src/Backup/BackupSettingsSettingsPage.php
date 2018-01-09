<?php

namespace Inpsyde\SearchAndReplace\Backup;

use Brain\Nonces\NonceInterface;
use Inpsyde\SearchAndReplace\Database\DatabaseBackup;
use Inpsyde\SearchAndReplace\File\FileDownloader;
use Inpsyde\SearchAndReplace\Settings\AbstractSettingsPage;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * Class BackupDatabase
 *
 * @package Inpsyde\SearchAndReplace\Page
 */
class BackupSettingsSettingsPage extends AbstractSettingsPage implements SettingsPageInterface {

	/**
	 * @var DatabaseBackup
	 */
	private $exporter;

	/**
	 * @var FileDownloader
	 */
	private $downloader;

	/**
	 * BackupSettingsPage constructor.
	 *
	 * @param DatabaseBackup $exporter
	 * @param FileDownloader $downloader
	 */
	public function __construct( DatabaseBackup $exporter, FileDownloader $downloader ) {

		$this->exporter   = $exporter;
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
	public function render( NonceInterface $nonce ) {

		?>
		<p>
			<?php esc_html_e(
				'Create a backup of your database by clicking "Create SQL File".',
				'search-and-replace'
			); ?>
		</p>
		<form action="" method="post">
			<?= \Brain\Nonces\formField( $nonce ) /* xss ok */ ?>
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
	 *
	 * @return bool
	 */
	public function save( array $request_data = [] ) {

		$report = $this->exporter->backup();
		$this->downloader->show_modal( $report );

		return TRUE;
	}

}
