<?php

namespace Inpsyde\SearchAndReplace\Settings\View;

use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Page
 */
interface SettingsPageViewInterface {

	/**
	 * Render all pages and handling save.
	 *
	 * @var SettingsPageInterface[] $pages
	 */
	public function render( array $pages = [] );

}
