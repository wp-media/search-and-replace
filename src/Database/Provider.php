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

		$plugin[ 'Database.Manager' ] = function () {

			return new Manager( $GLOBALS[ 'wpdb' ] );
		};

		$plugin[ 'Database.Replace' ] = function ( Container $plugin ) {

			return new Replace(
				$plugin[ 'Database.Manager' ],
				$plugin[ 'Service.MaxExecutionTime' ]
			);
		};

		$plugin[ 'Database.DatabaseImporter' ] = function ( Container $plugin ) {

			return new DatabaseImporter(
				$plugin[ 'config' ],
				$plugin[ 'Service.MaxExecutionTime' ]
			);
		};

		$plugin[ 'Database.DatabaseExporter' ] = function ( Container $plugin ) {

			return new DatabaseBackup(
				$plugin[ 'Database.Replace' ],
				$plugin[ 'Database.Manager' ],
				new \WP_Error()
			);
		};
	}

}

