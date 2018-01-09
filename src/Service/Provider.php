<?php

namespace Inpsyde\SearchAndReplace\Service;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * @package Inpsyde\SearchAndReplace\Service
 */
final class Provider implements ServiceProviderInterface {

	/**
	 * @param Container $plugin
	 */
	public function register( Container $plugin ) {

		$plugin[ 'Service.MaxExecutionTime' ] = function () {

			return new MaxExecutionTime();
		};
	}

}
