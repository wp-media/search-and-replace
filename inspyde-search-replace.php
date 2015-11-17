<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name:   Inpsyde Search & Replace
 * Plugin URI:    ${Plugin_Uri}
 * Description:
 * Author:        Inpsyde GmbH
 * Author URI:    http://inpsyde.com
 * Contributors:  s-hinse
 * Version:       0.0.1
 * Text Domain:
 * Domain Path:   /languages
 * License:       GPLv2 or later
 * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Inpsyde\SearchReplace;

$correct_php_version = version_compare( phpversion(), '5.3', '>=' );


#TODO show message into admin notice on plugin and run this on activation hook
if ( ! $correct_php_version ) {
	echo "This Plugin requires <strong>PHP 5.3</strong> or higher.<br>";
	echo "You are running PHP " . phpversion();
	exit;
}

#TODO run autoloade if plugin activated
//set up the autoloader
require_once( 'inc/Autoloader.php' );
$autoloader = new inc\Autoloader( __NAMESPACE__, __DIR__ );
$autoloader->register();

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

function init() {

	//this sets the capability needed to run the plugin
	//can be overridden by filter 'insr-capability'
	$cap = apply_filters( 'insr-capability', 'install_plugins' );

	if ( current_user_can( $cap ) ) {

		// Defines the path to the main plugin directory.
		$plugin_dir_url = plugin_dir_url( __FILE__ );
		define( 'INSR_DIR', $plugin_dir_url );

		//set up objects
		$dbm     = new inc\DatabaseManager();
		$replace = new inc\Replace( $dbm );
		$admin = new inc\Admin( $dbm, $replace ); #TODO remove not needed variables or use it

	}

}