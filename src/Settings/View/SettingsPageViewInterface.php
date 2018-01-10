<?php

namespace Inpsyde\SearchAndReplace\Settings\View;

use Inpsyde\SearchAndReplace\Http\Request;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Page
 */
interface SettingsPageViewInterface {

	/**
	 * Render all pages and handling save.
	 *
	 * @param SettingsPageInterface[] $pages
	 * @param Request                 $request
	 */
	public function render( array $pages = [], Request $request );

}
