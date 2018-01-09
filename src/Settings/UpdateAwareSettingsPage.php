<?php

namespace Inpsyde\SearchAndReplace\Settings;

/**
 * Interface SaveableSettingsPage
 *
 * @package Inpsyde\SearchAndReplace\Page
 */
interface UpdateAwareSettingsPage {

	/**
	 * Updating or using the send data.
	 *
	 * @param array $request_data
	 *
	 * @return boolean
	 */
	public function update( array $request_data = [] );
}
