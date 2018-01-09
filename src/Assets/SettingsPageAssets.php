<?php

namespace Inpsyde\SearchAndReplace\Assets;

use Inpsyde\SearchAndReplace\Core\PluginConfig;

class SettingsPageAssets {

	/**
	 * @var PluginConfig
	 */
	protected $config;

	/**
	 * AdminStyles constructor.
	 *
	 * @param PluginConfig $config
	 */
	public function __construct( PluginConfig $config ) {

		$this->config = $config;
	}

	/**
	 * Load the admin scripts.
	 *
	 * @return    bool
	 */
	public function register_scripts() {

		wp_enqueue_script(
			'search-and-replace-admin-scripts',
			$this->config->get( 'assets.js.url' ) . 'admin' . $this->config->get( 'assets.suffix' ) . '.js',
			[],
			$this->config->get( 'plugin.version' ),
			TRUE
		);

		return TRUE;
	}

	/**
	 * Load the admin style.
	 *
	 * @return    bool
	 */
	public function register_styles() {

		wp_enqueue_style(
			'search-and-replace-admin-styles',
			$this->config->get( 'assets.css.url' ) . 'admin' . $this->config->get( 'assets.suffix' ) . '.css',
			[],
			$this->config->get( 'plugin.version' )
		);

		return TRUE;
	}

}
