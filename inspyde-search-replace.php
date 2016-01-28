<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name:   Search & Replace
 * Plugin URI:    https://wordpress.org/plugins/search-and-replace/
 * Description:	  Search & Replace data in your whole WordPress setup, backup and import your database, change table prefix or migrate your domain to another domain.
 * Author:        Inpsyde GmbH
 * Author URI:    http://inpsyde.com
 * Contributors:  s-hinse @derpixler
 * Version:       3.0.1
 * Text Domain:   insr
 * Domain Path:   /languages
 * License:       GPLv2 or later
 * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die();

define( 'SR_PLUGIN_FILE', __FILE__ );

require dirname( __FILE__ ) . '/src/inspyde-search-replace.php';