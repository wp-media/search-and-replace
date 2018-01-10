<?php

namespace Inpsyde\SearchAndReplace\Credits;

use Inpsyde\SearchAndReplace\Settings\SettingsManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @package Inpsyde\GoogleTagManager\Backup
 */
final class Provider implements ServiceProviderInterface {

	/**
	 * @param Container $plugin
	 */
	public function register( Container $plugin ) {

		$plugin[ 'Credits.CreditsSettingsPage' ] = function ( Container $plugin ) {

			return new CreditsSettingsPage();
		};

		$plugin->extend(
			'Settings.SettingsManager',
			function ( SettingsManager $manager, Container $plugin ) {

				$manager->add_page( $plugin[ 'Credits.CreditsSettingsPage' ] );

				return $manager;
			}
		);
	}

}
