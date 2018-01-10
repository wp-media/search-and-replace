<?php

namespace Inpsyde\SearchAndReplace\Backup;

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

		$plugin[ 'Backup.BackupSettingsPage' ] = function ( Container $plugin ) {

			return new BackupSettingsPage(
				$plugin[ 'Settings.Auth.SettingsPageAuth' ],
				$plugin[ 'Database.DatabaseExporter' ],
				$plugin[ 'File.FileDownloader' ]
			);
		};

		$plugin->extend(
			'Settings.SettingsManager',
			function ( SettingsManager $manager, Container $plugin ) {

				$manager->add_page( $plugin[ 'Backup.BackupSettingsPage' ] );

				return $manager;
			}
		);
	}

}
