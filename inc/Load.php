<?php
namespace Inpsyde\SearchReplace;

use Requisite\Requisite;
use Requisite\Rule\Psr4;
use Requisite\SPLAutoLoader;

/**
 * Class Load handle plugin loading
 *
 * @since 3.1.1
 *
 * @package Inpsyde\SearchReplace
 */
class Load {

	/**
	 * Initialize the plugin autoload files
	 *
	 * @since 3.1.1
	 *
	 * @return void
	 */
	public function init(){

		search_replace_textdomain();

		if( $this->user_can_access() === TRUE ) {

			/**
			 * Load the Requisite library. Alternatively you can use composer's
			 */
			require_once __DIR__ . '/requisite/src/Requisite/Requisite.php';
			Requisite::init();

			$autoloader = new SPLAutoLoader;

			$autoloader->addRule(
				new Psr4(
					__DIR__,       // base directory
					'Inpsyde\SearchReplace' // base namespace
				)
			);

			$plugin = new Plugin();
			$plugin->run();
		}

	}

	/**
	 * Validate user access
	 * To change the user access capability use the filter search_replace_access_capability
	 * @see https://codex.wordpress.org/Roles_and_Capabilities
	 *
	 * @since 3.1.1
	 *
	 * @return bool
	 */
	private function user_can_access(){

		$user_cap = apply_filters( 'search_replace_access_capability', 'manage_options' );

		if ( current_user_can( $user_cap ) ) {
			return true;
		}

		return false;

	}

}
