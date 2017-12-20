<?php

namespace Inpsyde\SearchAndReplace\Core;

use Pimple\Container;

/**
 * @package Inpsyde\SearchAndReplace\Core
 */
interface BootableProviderInterface {

	/**
	 * Bootstraps the application.
	 *
	 * This method is called after all services are registered
	 * and should be used for "dynamic" configuration (whenever
	 * a service must be requested).
	 *
	 * @param Container $plugin
	 */
	public function boot( Container $plugin );
}
