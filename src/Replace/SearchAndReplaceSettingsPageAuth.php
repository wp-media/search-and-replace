<?php

namespace Inpsyde\SearchAndReplace\Replace;

use Inpsyde\SearchAndReplace\Http\Request;
use Inpsyde\SearchAndReplace\Settings\Auth\SettingsPageAuth;

/**
 * Class SearchAndReplaceSettingsPageAuth
 *
 * @package Inpsyde\SearchAndReplace\Replace
 */
class SearchAndReplaceSettingsPageAuth extends SettingsPageAuth {

	/**
	 * @param Request $request
	 *
	 * @return bool
	 */
	public function is_allowed( Request $request ) {

		if ( ! parent::is_allowed( $request ) ) {

			return FALSE;
		}

		$search  = $request->data()
			->get( 'search', '' );
		$replace = $request->data()
			->get( 'replace', '' );

		// if search field is empty and replace field is not empty quit. If both fields are empty, go on (useful for backup of single tables without changing)
		if ( '' === $search && '' !== $replace ) {

			$this->errors[] = __( 'Search field is empty.', 'search-and-replace' );

			return FALSE;
		}

		return TRUE;
	}

}