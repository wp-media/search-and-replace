<?php

namespace Inpsyde\SearchAndReplace\Replace;

use Inpsyde\SearchAndReplace\Settings\SettingsManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @package Inpsyde\GoogleTagManager\Replace
 */
final class Provider implements ServiceProviderInterface {

	/**
	 * @param Container $plugin
	 */
	public function register( Container $plugin ) {

		$plugin[ 'Replace.Domain.ReplaceDomainSettingsPage' ] = function ( Container $plugin ) {

			return new Domain\ReplaceDomainSettingsSettingsPage(
				$plugin[ 'Database.Manager' ],
				$plugin[ 'Database.DatabaseExporter' ],
				$plugin[ 'File.FileDownloader' ]
			);
		};

		$plugin[ 'Replace.SearchAndReplaceSettingsPage' ] = function ( Container $plugin ) {

			return new SearchAndReplaceSettingsSettingsPage(
				$plugin[ 'Database.Manager' ],
				$plugin[ 'Database.Replace' ],
				$plugin[ 'Database.DatabaseExporter' ],
				$plugin[ 'File.FileDownloader' ]
			);
		};

		$plugin->extend(
			'Settings.SettingsManager',
			function ( SettingsManager $manager, Container $plugin ) {

				$manager->add_page( $plugin[ 'Replace.SearchAndReplaceSettingsPage' ] );
				$manager->add_page( $plugin[ 'Replace.Domain.ReplaceDomainSettingsPage' ] );

				return $manager;
			}
		);
	}

}
