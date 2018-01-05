<?php

namespace Inpsyde\SearchAndReplace\Settings\Auth;

use Brain\Nonces\NonceInterface;

interface SettingsPageAuthInterface {

	/**
	 * @param array $request_data
	 *
	 * @return bool
	 */
	public function is_allowed( array $request_data = [] );

	/**
	 * @return NonceInterface
	 */
	public function nonce();

	/**
	 * @return string
	 */
	public function cap();
}