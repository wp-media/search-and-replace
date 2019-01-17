<?php

namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database;
use Inpsyde\SearchReplace\FileDownloader;

/**
 * Class ReplaceDomain
 *
 * @package Inpsyde\SearchReplace\inc\Page
 */
class ReplaceDomain extends AbstractPage implements PageInterface {

	/**
	 * @var \Inpsyde\SearchReplace\Database\Exporter
	 */
	private $dbe;

	/**
	 * @var Manager
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
	 * @return bool
	 * @throws \Throwable
	 */
	public function save() {

		$search        = esc_url_raw( filter_input( INPUT_POST, 'search' ) );
		$replace       = esc_url_raw( filter_input( INPUT_POST, 'replace' ) );
		$new_db_prefix = esc_attr( filter_input( INPUT_POST, 'new_db_prefix' ) );

		// search field should not be empty
		if ( '' === $replace ) {
			$this->add_error( esc_html__( 'Replace Field should not be empty.', 'search-and-replace' ) );

			return false;
		}

		// Do not pass the new db prefix if `change_db_prefix` isn't flagged.
		// @codingStandardsIgnoreStart
		$change_db_prefix = isset( $_POST[ 'change_db_prefix' ] ) ?
			filter_var( $_POST[ 'change_db_prefix' ], FILTER_VALIDATE_BOOLEAN ) :
			false;
		// @codingStandardsIgnoreEnd

		$new_db_prefix = $change_db_prefix ? $new_db_prefix : '';

		// Make the backup.
		$report = $this->dbe->db_backup( $search, $replace, [], true, $new_db_prefix );

		// Show the replace report.
		$this->downloader->show_modal( $report );

		return true;
	}

	/**
	 * Shows the page template
	 */
	public function render() {

		require_once __DIR__ . '/../templates/replace-domain.php';
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Do Replace Domain/Url', 'search-and-replace' );
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return esc_html__( 'Replace Domain URL', 'search-and-replace' );
	}

	/**
	 * Return the static slug string.
	 *
	 * @return string
	 */
	public function get_slug() {

		return 'replace-domain-url';
	}
}
