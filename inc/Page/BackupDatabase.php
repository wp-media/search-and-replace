<?php
namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database;
use Inpsyde\SearchReplace\FileDownloader;

/**
 * Class BackupDatabase
 *
 * @package Inpsyde\SearchReplace\Page
 */
class BackupDatabase extends AbstractPage implements PageInterface {

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
	 *shows the page template
	 */
	public function render() {

		require_once( __DIR__ . '/../templates/db_backup.php' );
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Create SQL File', 'search-and-replace' );
	}

	/**
	 * event handler for click on export sql button
	 */
	public function save() {

		$report = $this->dbe->db_backup();
		$this->downloader->show_modal( $report );

		return TRUE;
	}

}