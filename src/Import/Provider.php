<?php

namespace Inpsyde\SearchAndReplace\Import;

use Inpsyde\SearchAndReplace\Core\BootableProviderInterface;
use Inpsyde\SearchAndReplace\Settings\SettingsManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @package Inpsyde\GoogleTagManager\Import
 */
final class Provider implements ServiceProviderInterface {

	/**
	 * @param Container $plugin
	 */
	public function register( Container $plugin ) {

		$plugin[ 'Backup.ImportSettingsPage' ] = function ( Container $plugin ) {

			// TODO
			//return new ImportSettingsPage( $plugin[ 'config' ] );
		};

		$plugin->extend(
			'Settings.SettingsManager',
			function ( SettingsManager $manager, Container $plugin ) {

				//$manager->add_page( $plugin[ 'Backup.ImportSettingsPage' ] );

				return $manager;
			}
		);
	}

}
