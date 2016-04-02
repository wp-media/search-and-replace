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

	public function display_errors();
	public function get_menu_title();
	public function get_page_title();
	public function get_slug();
	public function render();
	public function save();
}