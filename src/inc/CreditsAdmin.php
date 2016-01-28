<?php
/**
 * Load credits template.
 */

namespace Inpsyde\SearchReplace\inc;

class CreditsAdmin extends Admin {

	/**
	 * Callback function for credits content.
	 */
	public function show_page() {

		require_once( 'templates/credits.php' );
	}

}