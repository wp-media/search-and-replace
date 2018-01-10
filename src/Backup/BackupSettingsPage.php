<?php

namespace Inpsyde\SearchAndReplace\Backup;

use Inpsyde\SearchAndReplace\Database\DatabaseBackup;
use Inpsyde\SearchAndReplace\File\FileDownloader;
use Inpsyde\SearchAndReplace\Http\Request;
use Inpsyde\SearchAndReplace\Settings\AbstractSettingsPage;
use Inpsyde\SearchAndReplace\Settings\Auth\SettingsPageAuthInterface;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;
use Inpsyde\SearchAndReplace\Settings\UpdateAwareSettingsPage;

/**
 * Class BackupDatabase
 *
 * @package Inpsyde\SearchAndReplace\Page
 */
class BackupSettingsPage extends AbstractSettingsPage implements SettingsPageInterface, UpdateAwareSettingsPage {

	/**
	 * @var DatabaseBackup
	 */
	private $exporter;

	/**
	 * @var FileDownloader
	 */
	private $downloader;

	/**
	 * @var SettingsPageAuthInterface
	 */
	private $auth;

	/**
	 * BackupSettingsPage constructor.
	 *
	 * @param SettingsPageAuthInterface $auth
	 * @param DatabaseBackup            $exporter
	 * @param FileDownloader            $downloader
	 */
	public function __construct( SettingsPageAuthInterface $auth, DatabaseBackup $exporter, FileDownloader $downloader ) {

		$this->auth       = $auth;
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
	 *
	 * @param Request $request
	 */
	public function render( Request $request ) {

		?>
		<p>
			<?php esc_html_e(
				'Create a backup of your database by clicking "Create SQL File".',
				'search-and-replace'
			); ?>
		</p>
		<form action="" method="post">
			<?= \Brain\Nonces\formField( $this->auth->nonce() ) /* xss ok */ ?>
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
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function update( Request $request ) {

		$report = $this->exporter->backup();
		$this->downloader->show_modal( $report );

		return TRUE;
	}

	/**
	 * @return SettingsPageAuthInterface
	 */
	public function auth() {

		return $this->auth;
	}

}
