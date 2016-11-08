<?php
namespace Inpsyde\SearchReplace;

/**
 * Class Plugin
 *
 * @package Inpsyde\SearchReplace\inc
 */
class Plugin {

	/**
	 * @param string $file : The path to the Plugin main file
	 */
	public function run() {

		global $wpdb;

		if ( is_admin() ) {

			// set max_execution_time to 0
			$RunTime = new Service\MaxExecutionTime();
			$RunTime->set();

			$dbm     = new Database\Manager( $wpdb );
			$replace = new Database\Replace( $dbm );
			$dbe     = new Database\Exporter( $replace, $dbm );
			$dbi     = new Database\Importer();

			$downloader = new FileDownloader( $dbe );
			add_action( 'init', array( $downloader, 'deliver_backup_file' ) );

			$page_manager = new Page\Manager();
			$page_manager->add_page( new Page\BackupDatabase( $dbe, $downloader ) );
			$page_manager->add_page( new Page\SearchReplace( $dbm, $replace, $dbe, $downloader ) );
			$page_manager->add_page( new Page\ReplaceDomain( $dbm, $dbe, $downloader ) );
			$page_manager->add_page( new Page\SqlImport( $dbi ) );
			$page_manager->add_page( new Page\Credits() );

			add_action( 'admin_menu', array( $page_manager, 'register_pages' ) );
			add_action( 'admin_head', array( $page_manager, 'remove_submenu_pages' ) );

			add_action( 'admin_enqueue_scripts', array( $page_manager, 'register_css' ) );
			add_action( 'admin_enqueue_scripts', array( $page_manager, 'register_js' ) );
		}

	}

}