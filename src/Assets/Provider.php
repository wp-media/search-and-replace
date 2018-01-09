<?php

namespace Inpsyde\SearchAndReplace\Assets;

use Inpsyde\SearchAndReplace\Core\BootableProviderInterface;
use Inpsyde\SearchAndReplace\Settings\SettingsManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @package Inpsyde\GoogleTagManager\Assets
 */
final class Provider implements ServiceProviderInterface, BootableProviderInterface {

	/**
	 * @param Container $plugin
	 */
	public function register( Container $plugin ) {

		$plugin[ 'Assets.SettingsPageAssets' ] = function ( Container $plugin ) {

			return new SettingsPageAssets( $plugin[ 'config' ] );
		};
	}

	/**
	 * @param Container $plugin
	 */
	public function boot( Container $plugin ) {

		if ( is_admin() ) {

			add_action(
				'admin_enqueue_scripts',
				[ $plugin[ 'Assets.SettingsPageAssets' ], 'register_scripts' ]
			);

			add_action(
				'admin_enqueue_scripts',
				[ $plugin[ 'Assets.SettingsPageAssets' ], 'register_styles' ]
			);

		}
	}

}
