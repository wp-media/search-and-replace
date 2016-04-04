<?php
namespace Inpsyde\SearchReplace;

use Inpsyde\SearchReplace\Database\Exporter;

/**
 * Class FileDownloader
 *
 * @package Inpsyde\SearchReplace
 */
class FileDownloader {

	/**
	 * @var Exporter
	 */
	protected $dbe;

	/**
	 * Admin constructor.
	 *
	 * @param Exporter $dbe
	 */
	public function __construct( Exporter $dbe ) {

		$this->dbe = $dbe;
	}

	/**
	 * calls the file delivery in Class DatabaseExporter
	 *
	 * @wp-hook init
	 */
	public function deliver_backup_file() {

		if ( ! $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
			return;
		}

		if ( ! isset( $_POST[ 'insr_nonce' ] ) || ! wp_verify_nonce( $_POST[ 'insr_nonce' ], 'download_sql' ) ) {
			return;
		}

		if ( isset( $_POST[ 'action' ] ) && 'download_file' === $_POST[ 'action' ] ) {

			$sql_file = '';
			if ( isset( $_POST[ 'sql_file' ] ) ) {
				$sql_file = $_POST[ 'sql_file' ];
			}

			$compress = FALSE;
			if ( isset( $_POST[ 'compress' ] ) ) {
				$compress = $_POST[ 'compress' ];
			}

			// If file name contains path or does not end with '.sql' exit.
			$ext = strrchr( $sql_file, '.' );
			if ( FALSE !== strpos( $sql_file, '/' ) || '.sql' !== $ext ) {
				die;
			}
			$this->dbe->deliver_backup( $sql_file, $compress );
		}

	}
}