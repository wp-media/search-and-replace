<?php

namespace Inpsyde\SearchReplace\Page;

/**
 * Interface PageInterface
 *
 * @package Inpsyde\SearchReplace\Page
 */
interface PageInterface {

	/**
	 * @param string $msg
	 */
	public function add_error( $msg );

	/**
	 * Echoes the content of the $errors array as formatted HTML if it contains error messages.
	 */
	public function display_errors();

	/**
	 * Returns the translated Menu title for add_submenu_page().
	 *
	 * @return string
	 */
	public function get_menu_title();

	/**
	 * Returns the translated title for the page.
	 *
	 * @return string
	 */
	public function get_page_title();

	/**
	 * Returns the page_slug for add_submenu_page().
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * rendering the page content.
	 */
	public function render();

	/**
	 * saving the data.
	 *
	 * @return boolean
	 */
	public function save();
}