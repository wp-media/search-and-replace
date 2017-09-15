<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name:  Search & Replace
 * Plugin URI:   https://wordpress.org/plugins/search-and-replace/
 * Description:  Search & Replace data in your whole WordPress setup, backup and import your database, change table prefix or migrate your domain to another domain.
 * Author:       Inpsyde GmbH Author
 * URI:          https://inpsyde.com
 * Contributors: s-hinse, derpixler, ChriCo, Bueltge, inpsyde
 * Version:      3.2.0-dev
 * Text Domain:  search-and-replace
 * Domain Path:  /languages
 * License:      GPLv3+
 * License URI:  license.txt
 */

use Requisite\Requisite;
use Requisite\Rule\Psr4;
use Requisite\SPLAutoLoader;
use Inpsyde\SearchReplace\Database as Database;
use Inpsyde\SearchReplace\Page as Page;

defined( 'ABSPATH' ) or die( 'No direct access!' );

add_action( 'plugins_loaded', 'search_replace_load' );
register_activation_hook( __FILE__, 'search_replace_activate' );

/**
 * Validate requirements on activation
 *
 * Runs on plugin activation.
 * Check if php min 5.4.0 if not deactivate the plugin.
 *
 * @since 3.1.1
 *
 * @return void
 */
function search_replace_activate() {

	$required_php_version = '5.4.0';
	$correct_php_version  = version_compare( phpversion(), $required_php_version, '>=' );

	search_replace_textdomain();

	if ( ! $correct_php_version ) {
		deactivate_plugins( basename( __FILE__ ) );

		wp_die(
			'<p>' .
			sprintf(
				esc_attr__(
					'This plugin can not be activated because it requires at least PHP version %1$s. ',
					'search-and-replace'
				),
				$required_php_version
			)
			. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . esc_attr__( 'back', 'search-and-replace' ) . '</a>'
		);

	}

}

/**
 * Load the plugin
 *
 * @since 3.1.1
 *
 * @return bool
 */
function search_replace_load() {

	global $wpdb;

	// all hooks are just available on backend.
	if ( ! is_admin() ) {
		return FALSE;
	}

	define( 'SEARCH_REPLACE_BASEDIR', plugin_dir_url( __FILE__ ) );

	search_replace_textdomain();

	$user_cap = apply_filters( 'search_replace_access_capability', 'manage_options' );
	if ( ! current_user_can( $user_cap ) ) {
		return FALSE;
	}

	$file = __DIR__ . '/vendor/autoload.php';
	if ( ! file_exists( $file ) ) {
		return FALSE;
	}
	include_once( $file );

	$max_execution = new Inpsyde\SearchReplace\Service\MaxExecutionTime();

	$dbm     = new Database\Manager( $wpdb );
	$replace = new Database\Replace( $dbm, $max_execution );
	$dbe     = new Database\Exporter( $replace, $dbm );
	$dbi     = new Database\Importer( $max_execution );

	$downloader = new Inpsyde\SearchReplace\FileDownloader( $dbe, $max_execution );
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

	return TRUE;
}

/**
 * Loading the plugin translations.
 */
function search_replace_textdomain() {

	return load_plugin_textdomain(
		'search-and-replace',
		FALSE,
		plugin_basename( __DIR__ ) . '/l10n/'
	);
}