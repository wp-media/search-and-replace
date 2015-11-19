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

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

function init() { new inc\Index(); }