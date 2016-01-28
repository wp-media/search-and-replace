<?php
/**
 *
 */

namespace Inpsyde\SearchReplace\inc;

class CreditsAdmin extends Admin {

	/**
	 *callback function for menu item
	 */
	public function show_page() {

		require_once( 'templates/credits.php' );
	}

}