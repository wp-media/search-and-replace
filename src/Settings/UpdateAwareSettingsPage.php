<?php

namespace Inpsyde\SearchAndReplace\Settings;

use Inpsyde\SearchAndReplace\Http\Request;
use Inpsyde\SearchAndReplace\Settings\Auth\SettingsPageAuthInterface;

/**
 * Interface SaveableSettingsPage
 *
 * @package Inpsyde\SearchAndReplace\Page
 */
interface UpdateAwareSettingsPage {

	/**
	 * Updating or using the send data.
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function update( Request $request );
}
