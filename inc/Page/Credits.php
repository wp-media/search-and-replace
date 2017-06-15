<?php
namespace Inpsyde\SearchReplace\Page;

/**
 * Class Credits
 *
 * @package Inpsyde\SearchReplace\Page
 */
class Credits extends AbstractPage implements PageInterface {

	/**
	 * Callback function for credits content.
	 */
	public function render() {

		require_once( __DIR__ . '/../templates/credits.php' );
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return esc_html__( 'Credits', 'search-and-replace' );
	}

	/**
	 * Return the static slug string.
	 *
	 * @return string
	 */
	public function get_slug() {

		return 'credits';
	}

	/**
	 * @return boolean
	 */
	public function save() {

		return TRUE;
	}
}
