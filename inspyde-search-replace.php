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

register_activation_hook( __FILE__, __NAMESPACE__ . '\insr_activate' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

function insr_activate() {

	$correct_php_version = version_compare( phpversion(), '5.3', '>=' );

	if ( ! $correct_php_version ) {
		deactivate_plugins( basename( __FILE__ ) );

		wp_die(
			'<p>' .
			sprintf(
				__( 'This plugin can not be activated because it requires at least PHP version %1$s. ', 'insr' ),
				5.3
			)
			. '</p> <a href="' . admin_url( 'plugins.php' ) . '">' . __( 'back', 'insr' ) . '</a>'
		);

	}
}

function init() {

	//this sets the capability needed to run the plugin
	$cap = 'manage_options';

	if ( current_user_can( $cap ) ) {
		//set up the autoloader
		require_once( 'inc/Autoloader.php' );

		$autoloader = new inc\Autoloader( __NAMESPACE__, __DIR__ );
		$autoloader->register();

		//start the plugin
		$plugin = new inc\Init();
		$plugin->run( __FILE__ );

	}

}