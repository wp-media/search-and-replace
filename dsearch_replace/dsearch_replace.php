<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              daffodilsw.com
 * @since             1.0.0
 * @package           Dsearch_replace
 *
 * @wordpress-plugin
 * Plugin Name:       dsearchreplace
 * Plugin URI:        daffodilsw.com
 * Description:       Repalce the serached value with the replace string from the selected tables. Go to the Search Replace menu.
 * Version:           1.0.0
 * Author:            Abhishek Gupta
 * Author URI:        daffodilsw.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dsearch_replace
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-dsearch_replace-activator.php
 */
function activate_dsearch_replace() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dsearch_replace-activator.php';
	Dsearch_replace_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-dsearch_replace-deactivator.php
 */
function deactivate_dsearch_replace() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dsearch_replace-deactivator.php';
	Dsearch_replace_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_dsearch_replace' );
register_deactivation_hook( __FILE__, 'deactivate_dsearch_replace' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-dsearch_replace.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_dsearch_replace() {

	$plugin = new Dsearch_replace();
	$plugin->run();

}
run_dsearch_replace();
