<?php

namespace Inpsyde\SearchAndReplace\Settings;

use Inpsyde\SearchAndReplace\Http\Request;
use Inpsyde\SearchAndReplace\Settings\Auth\SettingsPageAuth;
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
	 * @var SettingsPageAuth
	 */
	private $auth;

	/**
	 * @var static
	 */
	private $request;

	/**
	 * @param SettingsPageViewInterface $view
	 */
	public function __construct( SettingsPageViewInterface $view, SettingsPageAuth $auth ) {

		$this->view    = $view;
		$this->auth    = $auth;
		$this->request = Request::from_globals();
	}

	/**
	 * Register all Pages.
	 *
	 * @wp-hook admin_menu
	 */
	public function register() {

		foreach ( $this->pages as $slug => $page ) {

			$cap = $page instanceof UpdateAwareSettingsPage
				? $page->auth()
					->cap()
				: $this->auth->cap();

			$hook = add_submenu_page(
				'tools.php',
				$page->get_page_title(),
				$page->get_menu_title(),
				$cap,
				$slug,
				function () {

					$this->view->render( $this->pages, $this->request );
				}
			);

			if ( $page instanceof UpdateAwareSettingsPage ) {
				add_action( 'load-' . $hook, [ $this, 'save' ] );
			}
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

		if ( $this->request->server()->get( 'REQUEST_METHOD' ) !== 'POST' ) {

			return FALSE;
		}

		$page_slug = $this->request->query()
			->get( 'page', '' );

		if ( $page_slug === '' || ! isset( $this->pages[ $page_slug ] ) ) {

			return FALSE;
		}

		/** @var SettingsPageInterface|UpdateAwareSettingsPage $page */
		$page = $this->pages[ $page_slug ];

		if ( ! $page->auth()
			->is_allowed( $this->request ) ) {

			array_walk(
				$page->auth()
					->errors(),
				[ $page, 'add_error' ]
			);

			return FALSE;
		}

		return $page->update( $this->request );
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
