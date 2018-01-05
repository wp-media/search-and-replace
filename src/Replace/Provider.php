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

			// TODO
			//return new Domain\ReplaceDomainSettingsPage( $plugin[ 'config' ] );
		};

		$plugin[ 'Replace.SearchAndReplaceSettingsPage' ] = function (Container $plugin ) {

			// TODO
			//return new SearchAndReplaceSettingsPage( $plugin[ 'config' ] );
		};

		$plugin->extend(
			'Settings.SettingsManager',
			function ( SettingsManager $manager, Container $plugin ) {

				//$manager->add_page( $plugin[ 'Replace.SearchAndReplaceSettingsPage' ] );
				//$manager->add_page( $plugin[ 'Replace.Domain.ReplaceDomainSettingsPage' ] );

				return $manager;
			}
		);
	}

}
