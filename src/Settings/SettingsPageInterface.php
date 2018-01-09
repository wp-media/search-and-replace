<?php

namespace Inpsyde\SearchAndReplace\Settings;

use Brain\Nonces\NonceInterface;

/**
 * Interface PageInterface
 *
 * @package Inpsyde\SearchAndReplace\Page
 */
interface SettingsPageInterface {

	/**
	 * @param string $msg
	 */
	public function add_error( $msg );

	/**
	 * Echoes the content of the $errors array as formatted HTML if it contains error messages.
	 */
	public function render_notifications();

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
	 * Rendering the page content.
	 *
	 * @param NonceInterface $nonce
	 */
	public function render( NonceInterface $nonce );

	/**
	 * Saving the data.
	 *
	 * @param array $request_data
	 *
	 * @return boolean
	 */
	public function save( array $request_data = [] );
}
