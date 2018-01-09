<?php

namespace Inpsyde\SearchAndReplace\Settings;

use Inpsyde\SearchAndReplace\Core\BootableProviderInterface;
use Inpsyde\SearchAndReplace\Settings\Auth\SettingsPageAuth;
use Inpsyde\SearchAndReplace\Settings\Auth\SettingsPageAuthInterface;
use Inpsyde\SearchAndReplace\Settings\View\SettingsPageView;
use Inpsyde\SearchAndReplace\Settings\View\SettingsPageViewInterface;
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

		$plugin[ 'Settings.View.SettingsPageView' ] = function ( Container $plugin ): SettingsPageViewInterface {

			return new SettingsPageView( $plugin[ 'config' ] );
		};

		$plugin[ 'Settings.Auth.SettingsPageAuth' ] = function ( Container $plugin ): SettingsPageAuthInterface {

			return new SettingsPageAuth( $plugin[ 'config' ]->get( 'plugin.textdomain' ) );
		};

		$plugin[ 'Settings.SettingsManager' ] = function ( Container $plugin ): SettingsManager {

			return new SettingsManager(
				$plugin[ 'Settings.View.SettingsPageView' ],
				$plugin[ 'Settings.Auth.SettingsPageAuth' ]
			);
		};

	}

	/**
	 * @param Container $plugin
	 */
	public function boot( Container $plugin ) {

		add_action( 'admin_menu', [ $plugin[ 'Settings.SettingsManager' ], 'register' ] );
		add_action( 'admin_head', [ $plugin[ 'Settings.SettingsManager' ], 'unregister_submenu_pages' ] );

	}

}
