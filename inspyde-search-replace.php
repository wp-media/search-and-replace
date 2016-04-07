<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name:  Search & Replace
 * Plugin URI:   https://wordpress.org/plugins/search-and-replace/
 * Description:  Search & Replace data in your whole WordPress setup, backup and import your database, change table prefix or migrate your domain to another domain.
 * Author:       Inpsyde GmbH
 * Author URI:   http://inpsyde.com
 * Contributors: s-hinse, derpixler, ChriCo, Bueltge, inpsyde
 * Version:      3.1.0
 * Text Domain:  search-and-replace
 * Domain Path:  /languages
 * License:      GPLv3+
 * License URI:  license.txt
 */

namespace Inpsyde\SearchReplace;

use Requisite\Requisite;
use Requisite\Rule\Psr4;
use Requisite\SPLAutoLoader;

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Register textdomain.
 */
function load_textdomain() {

	$lang_dir = plugin_basename( __DIR__ ) . '/l10n/';

	load_plugin_textdomain( 'search-and-replace', FALSE, $lang_dir );
}

/**
 * Run on plugin activation, checks requirements.
 */
function activate() {

	load_textdomain();

	$required_php_version = '5.4.0';
	$correct_php_version  = version_compare( phpversion(), $required_php_version, '>=' );

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



/**
 * Load and init in WP Environment.
 */
function init() {

	if ( ! is_admin() ) {
		return;
	}
	// This sets the capability needed to run the plugin.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	load_textdomain();

	/**
	 * Load the Requisite library. Alternatively you can use composer's
	 */
	require_once __DIR__ . '/inc/requisite/src/Requisite/Requisite.php';
	Requisite::init();

	$autoloader = new SPLAutoLoader;

	$autoloader->addRule(
		new Psr4(
			__DIR__ . '/inc',       // base directory
			'Inpsyde\SearchReplace' // base namespace
		)
	);


	// Start the plugin.
	$plugin = new Plugin();
	$plugin->run( __FILE__ );
}
