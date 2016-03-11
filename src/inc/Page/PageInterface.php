<?php

namespace Inpsyde\SearchReplace\Page;

/**
 * Interface PageInterface
 *
 * @package Inpsyde\SearchReplace\Page
 */
interface PageInterface {

	public function get_menu_title();
	public function get_page_title();
	public function get_slug();
	public function render();
	public function save();
}