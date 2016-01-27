<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name:   Inpsyde Search & Replace
 * Plugin URI:    https://wordpress.org/plugins/search-and-replace/
 * Description:	  Search & Replace data in your WordPress. Backup & import your database. Change tableprefix. Change your domain
 * Author:        Inpsyde GmbH
 * Author URI:    http://inpsyde.com
 * Contributors:  s-hinse @derpixler
 * Version:       3.0.0
 * Text Domain:   insr
 * Domain Path:   /languages
 * License:       GPLv2 or later
 * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die();

define( 'SR_PLUGIN_FILE', __FILE__ );

require dirname( __FILE__ ) . '/src/inspyde-search-replace.php';
