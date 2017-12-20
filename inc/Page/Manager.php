<?php

namespace Inpsyde\SearchReplace\Page;

/**
 * Class Manager
 *
 * @package Inpsyde\SearchReplace\Page
 */
class Manager {

	/**
	 * @var PageInterface[]
	 */
	private $pages = [];

	/**
	 * Add page.
	 *
	 * @param PageInterface $page
	 */
	public function add_page( PageInterface $page ) {

		$this->pages[ $page->get_slug() ] = $page;
	}

	/**
	 * Handling the POST-Request and save the data.
	 */
	public function save() {

		if ( $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
			return;
		}

		$page = filter_input( INPUT_POST, 'action' );
		if ( '' === $page ) {
			return;
		}

		if ( ! isset( $this->pages[ $page ] ) ) {
			return;
		}

		if ( ! check_admin_referer( 'replace_domain', 'insr_nonce' ) ) {
			return;
		}

		/** @var PageInterface */
		$this->pages[ $page ]->save();
	}

	/**
	 * Register all Pages.
	 *
	 * @wp-hook admin_menu
	 */
	public function register_pages() {

		foreach ( $this->pages as $slug => $page ) {

			/**
			 * @param string        $cap
			 * @param PageInterface $page
			 */
			$cap = apply_filters( 'insr-capability', 'manage_options', $page );

			add_submenu_page(
				'tools.php',
				$page->get_page_title(),
				$page->get_menu_title(),
				$cap,
				$slug,
				[ $this, 'render' ]
			);
		}
	}

	/**
	 * Removes the plugins sub-menu pages from admin menu.
	 *
	 * @wp-hook admin_head
	 */
	public function remove_submenu_pages() {

		$i = 0;
		foreach ( $this->pages as $slug => $page ) {
			if ( $i > 0 ) {
				remove_submenu_page( 'tools.php', $slug );
			}
			$i ++;
		}
	}

	/**
	 * Render all pages and handling save.
	 */
	public function render() {

		$url          = admin_url( 'tools.php' );
		$current_page = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : key( $this->pages );

		$output = '<div class="wrap">';
		$output .= '<h1 id="title">' . esc_html__( 'Search & Replace', 'search-and-replace' ) . '</h1>';
		$output .= '<h2 class="nav-tab-wrapper">';

		foreach ( $this->pages as $slug => $page ) :
			$class  = $current_page === $slug ? 'nav-tab-active' : '';
			$output .= sprintf(
				'<a class="nav-tab %1$s" href="%2$s">%3$s</a>',
				esc_attr( $class ),
				add_query_arg( 'page', $slug, $url ),
				$page->get_page_title()
			);
		endforeach;
		unset( $page );

		$output .= '</h2>';

		// Set the current page.
		$page = $this->pages[ $current_page ];

		echo $output;
		echo '<div class="tab__content">';
		$this->save();
		$page->display_errors();
		$page->render();
		echo '</div>';
		echo '</div>'; // wrap
	}

	/**
	 * Registers the Plugin stylesheet.
	 *
	 * @wp-hook admin_enqueue_scripts
	 */
	public function register_css() {

		if ( ! $this->is_search_and_replace_admin_page() ) {
			return;
		}

		$suffix = $this->get_script_suffix();
		$url    = ( SEARCH_REPLACE_BASEDIR . '/assets/css/inpsyde-search-replace' . $suffix . '.css' );
		$handle = 'insr-styles';

		wp_register_script( $handle, $url );
		wp_enqueue_style( $handle, $url, [], false, false );
	}

	/**
	 * Registers the Plugin javascript.
	 *
	 * @wp-hook admin_enqueue_scripts
	 */
	public function register_js() {

		if ( ! $this->is_search_and_replace_admin_page() ) {
			return;
		}

		$suffix = $this->get_script_suffix();
		$url    = ( SEARCH_REPLACE_BASEDIR . '/assets/js/inpsyde-search-replace' . $suffix . '.js' );
		$handle = 'insr-js';

		wp_register_script( $handle, $url );
		wp_enqueue_script( $handle, $url, [], false, true );
	}

	/**
	 * Get script suffix to difference between live and debug files.
	 *
	 * @return string
	 */
	private function get_script_suffix() {

		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * Is admin search and replace page
	 *
	 * Check against the current screen in admin,
	 *
	 * @return bool True if current screen is one of the search and replace pages
	 */
	private function is_search_and_replace_admin_page() {

		$current = str_replace( 'tools_page_', '', get_current_screen()->id );

		return array_key_exists( $current, $this->pages );
	}
}
