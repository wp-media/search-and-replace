<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name:  Search & Replace
 * Plugin URI:   https://wordpress.org/plugins/search-and-replace/
 * Description:  Search & Replace data in your whole WordPress setup, backup and import your database, change table prefix or migrate your domain to another domain.
 * Author:       Inpsyde GmbH Author
 * URI:          https://inpsyde.com
 * Contributors: inpsyde, Bueltge, ChriCo
 * Version:      3.2.0-dev
 * Text Domain:  search-and-replace
 * Domain Path:  /languages
 * License:      GPLv3+
 * License URI:  license.txt
 */

namespace Inpsyde\SearchAndReplace;

if ( ! function_exists( 'add_filter' ) ) {
	return;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\initialize' );

/**
 * @wp-hook plugins_loaded
 *
 * @throws \Throwable   When WP_DEBUG=TRUE exceptions will be thrown.
 */
function initialize() {

	try {

		load_plugin_textdomain( 'search-and-replace' );

		if ( ! check_plugin_requirements() ) {

			return FALSE;
		}

		$plugin = new SearchAndReplace();

		$plugin->register( new Exporter\Provider() );

		$plugin->boot();

	}
	catch ( \Throwable $e ) {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			throw $e;
		}

		do_action( 'search-and-replace.error', $e );

		return FALSE;
	}

	return TRUE;
}

/**
 * @return bool
 */
function check_plugin_requirements() {

	$min_php_version     = '7.0';
	$current_php_version = phpversion();
	if ( ! version_compare( $current_php_version, $min_php_version, '>=' ) ) {
		admin_notice(
			sprintf(
			/* translators: %1$s is the min PHP-version, %2$s the current PHP-version */
				__(
					'Search & Replace requires PHP version %1$1s or higher. You are running version %2$2s.',
					'search-and-replace'
				),
				$min_php_version,
				$current_php_version
			)
		);

		return FALSE;
	}

	if ( ! class_exists( SearchAndReplace::class ) ) {
		$autoloader = __DIR__ . '/vendor/autoload.php';
		if ( file_exists( $autoloader ) ) {
			/** @noinspection PhpIncludeInspection */
			require $autoloader;
		} else {

			admin_notice(
				__(
					'Could not find a working autoloader for Search and Replace.',
					'search-and-replace'
				)
			);

			return FALSE;
		}
	}

	return TRUE;
}

/**
 * @param string $message
 */
function admin_notice( $message ) {

	add_action(
		'admin_notices',
		function () use ( $message ) {

			printf(
				'<div class="notice notice-error"><p>%1$s</p></div>',
				esc_html( $message )
			);
		}
	);

}
