<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name:  Search & Replace
 * Plugin URI:   https://wordpress.org/plugins/search-and-replace/
 * Description:  Search & Replace data in your whole WordPress setup, backup and import your database, change table prefix or migrate your domain to another domain.
 * Author:       Inpsyde GmbH
 * Author URI:   http://inpsyde.com
 * Contributors: s-hinse, derpixler, ChriCo, Bueltge, inpsyde
 * Version:      3.1.2
 * Text Domain:  search-and-replace
 * Domain Path:  /languages
 * License:      GPLv3+
 * License URI:  license.txt
 */


defined( 'ABSPATH' ) or die( 'No direct access!' );

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

	global $l10n, $l10n_unloaded;

	$required_php_version = '5.4.0';
	$correct_php_version  = version_compare( phpversion(), $required_php_version, '>=' );

	search_replace_textdomain();

	if ( ! $correct_php_version ) {
		deactivate_plugins( basename( __FILE__ ) );

		wp_die(
			'<p>' .
			sprintf(
				esc_attr__( 'This plugin can not be activated because it requires at least PHP version %1$s. ', 'search-and-replace' ),
				$required_php_version
			)
			. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . esc_attr__( 'back', 'search-and-replace' ) . '</a>'
		);

	}

}

register_activation_hook( __FILE__, 'search_replace_activate' );


/**
 * Load the plugin
 *
 * @since 3.1.1
 *
 * @return void
 */
function search_replace_load(){

	$load = __DIR__ . '/inc/Load.php';

	if ( file_exists( $load ) ) {
		require_once $load;

		define( 'SEARCH_REPLACE_BASEDIR', plugin_dir_url( __FILE__ ) );

		$load = new \Inpsyde\SearchReplace\Load();
		$load->init();
	}

}

add_action( 'plugins_loaded', 'search_replace_load' );

/**
 * Load plugins translations
 */
function search_replace_textdomain(){

	$lang_dir = plugin_basename( __DIR__ ) . '/l10n/';
	load_plugin_textdomain( 'search-and-replace', FALSE, $lang_dir );

}