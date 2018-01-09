<?php

namespace Inpsyde\SearchAndReplace\File;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class Provider
 *
 * @package Inpsyde\SearchAndReplace\File
 */
final class Provider implements ServiceProviderInterface {

	/**
	 * @param Container $plugin
	 */
	public function register( Container $plugin ) {

		$plugin[ 'File.FileDownloader' ] = function ( Container $plugin ) {

			return new FileDownloader( $plugin[ 'Service.MaxExecutionTime' ] );
		};
	}

}

