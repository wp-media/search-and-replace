<?php

namespace Inpsyde\SearchAndReplace\Database;

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

		$plugin[ 'Database.Importer' ] = function ( Container $plugin ) {

			return new Importer( $plugin[ 'Service.MaxExecutionTime' ] );
		};

	}

}

