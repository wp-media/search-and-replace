<?php

namespace Inpsyde\SearchAndReplace\Settings;

use Inpsyde\SearchAndReplace\Settings\Auth\SettingsPageAuthInterface;
use Inpsyde\SearchAndReplace\Settings\View\SettingsPageView;
use Inpsyde\SearchAndReplace\Settings\View\SettingsPageViewInterface;

/**
 * @package Inpsyde\SearchAndReplace\Page
 */
class SettingsManager {

	/**
	 * @var SettingsPageInterface[]
	 */
	private $pages = [];

	/**
	 * @var SettingsPageView
	 */
	private $view;

	/**
	 * @var SettingsPageAuthInterface
	 */
	private $auth;

	/**
	 * @param SettingsPageViewInterface $view
	 * @param SettingsPageAuthInterface $auth
	 */
	public function __construct( SettingsPageViewInterface $view, SettingsPageAuthInterface $auth ) {

		$this->view = $view;
		$this->auth = $auth;
	}

	/**
	 * Register all Pages.
	 *
	 * @wp-hook admin_menu
	 */
	public function register() {

		foreach ( $this->pages as $slug => $page ) {

			$hook = add_submenu_page(
				'tools.php',
				$page->get_page_title(),
				$page->get_menu_title(),
				$this->auth->cap( $page ),
				$slug,
				function () {

					$this->view->render( $this->pages, $this->auth->nonce() );
				}
			);

			add_action( 'load-' . $hook, [ $this, 'save' ] );
		}
	}

	/**
	 * Add page.
	 *
	 * @param SettingsPageInterface $page
	 */
	public function add_page( SettingsPageInterface $page ) {

		$this->pages[ $page->get_slug() ] = $page;
	}

	/**
	 * Handling the POST-Request and save the data.
	 *
	 * @return bool
	 */
	public function save() {

		$request_data = $_POST;
		$page         = $_GET[ 'page' ] ? : '';

		if ( $page === '' || ! isset( $this->pages[ $page ] ) ) {

			return FALSE;
		}

		if ( ! $this->auth->is_allowed( $request_data ) ) {

			return FALSE;
		}

		/** @var SettingsPageInterface */
		return $this->pages[ $page ]->save( $request_data );
	}

	/**
	 * Removes the plugins sub-menu pages from admin menu.
	 *
	 * @wp-hook admin_head
	 */
	public function unregister_submenu_pages() {

		$i = 0;
		foreach ( $this->pages as $slug => $page ) {
			if ( $i > 0 ) {
				remove_submenu_page( 'tools.php', $slug );
			}
			$i ++;
		}
	}

}
