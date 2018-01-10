<?php

namespace Inpsyde\SearchAndReplace\Settings\Auth;

use Brain\Nonces\NonceInterface;
use Inpsyde\SearchAndReplace\Http\Request;

/**
 * Interface SettingsPageAuthInterface
 *
 * @package Inpsyde\SearchAndReplace\Settings\Auth
 */
interface SettingsPageAuthInterface {

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function is_allowed( Request $request );

	/**
	 * @return NonceInterface
	 */
	public function nonce();

	/**
	 * @return string
	 */
	public function cap();

	/**
	 * Returns a collection of arrows which occurred.
	 *
	 * @return array
	 */
	public function errors();
}