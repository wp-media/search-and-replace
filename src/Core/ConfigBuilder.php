<?php

namespace Inpsyde\SearchAndReplace\Core;

/**
 * @package Inpsyde\SearchAndReplace\Core
 */
final class ConfigBuilder {
	/**
	 * Creating the Plugin-Config by given $file in constructor.
	 *
	 * @param string $file
	 *
	 * @return PluginConfig $config
	 */
	public static function from_file( $file ): PluginConfig {
		$config = new PluginConfig();
		$config->import(
			[
				'debug.display'     => defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY,
				'debug.mode'        => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'debug.script_mode' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
			]
		);
		$config->import( self::get_plugin_headers( $file ) );
		$config->import(
			[
				'assets.suffix'  => $config->get( 'debug.mode' ) ? '' : '.min',
				'assets.css.url' => $config->get( 'plugin.url' ) . 'assets/css/',
				'assets.js.url'  => $config->get( 'plugin.url' ) . 'assets/js/',
				'assets.img.url' => $config->get( 'plugin.url' ) . 'assets/img/',
				'assets.css.dir' => $config->get( 'plugin.dir' ) . 'assets/css/',
				'assets.js.dir'  => $config->get( 'plugin.dir' ) . 'assets/js/',
				'assets.img.dir' => $config->get( 'plugin.dir' ) . 'assets/img/',
			]
		);
		return $config;
	}
	/**
	 * Internal function to create the plugin_headers for $config.
	 *
	 * @param string $file
	 *
	 * @return array $plugin_headers
	 */
	private static function get_plugin_headers( $file ): array {
		if ( defined( 'ABSPATH' ) ) {
			$plugins_file = ABSPATH . '/wp-admin/includes/plugin.php';
			if ( ! function_exists( 'get_config' ) && file_exists( $plugins_file ) ) {
				require_once( $plugins_file );
			}
		}
		$default_headers = [
			'plugin.name'            => 'Plugin Name',
			'plugin.uri'             => 'Plugin URI',
			'plugin.description'     => 'Description',
			'plugin.author'          => 'Author',
			'plugin.version'         => 'Version',
			'plugin.textdomain'      => 'Text Domain',
			'plugin.textdomain.path' => 'Domain Path',
		];
		$headers = get_file_data( $file, $default_headers );
		$headers[ 'plugin.dir' ]  = plugin_dir_path( $file );
		$headers[ 'plugin.file' ] = $file;
		$headers[ 'plugin.url' ]  = plugins_url( '/', $file );
		return $headers;
	}
}