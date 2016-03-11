<?php
namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database\Exporter;
use Inpsyde\SearchReplace\Database\Manager;

/**
 * Class BackupDatabase
 *
 * @package Inpsyde\SearchReplace\Page
 */
class BackupDatabase extends AbstractPage implements PageInterface {

	/**
	 * @var Exporter
	 */
	private $dbe;

	/**
	 * BackupDatabase constructor.
	 *
	 * @param Exporter $dbe
	 */
	public function __construct( Exporter $dbe ) {
		$this->dbe = $dbe;
	}

	/**
	 * @return string
	 */
	public function get_menu_title() {

		return __( 'Backup Database', 'search-and-replace' );
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return __( 'Backup Database', 'search-and-replace' );
	}

	/**
	 *shows the page template
	 */
	public function render() {

		require_once(  __DIR__ . '/../templates/db_backup.php' );
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return __( 'Create SQL File', 'search-and-replace' );
	}

	/**
	 * event handler for click on export sql button
	 */
	public function save( ) {
		$this->dbe->create_backup_file( );
	}

}