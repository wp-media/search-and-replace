<?php
namespace Inpsyde\SearchReplace\inc;

class Init {

	private static $plugin_pages = array(
		'tools_page_inpsyde_search_replace',
		'tools_page_db_backup',
		'tools_page_sql_import',
		'tools_page_replace_domain',
		'tools_page_credits',
	);

	/**
	 * @var String  contains 'min'-suffix for css and js files in live mode
	 */
	private $suffix;

	/**
	 * @param string $file : The path to the Plugin main file
	 */
	public function run( $file ) {

		//Defines the path to the main plugin directory.
		$plugin_dir_url = plugin_dir_url( $file );
		define( 'INSR_DIR', $plugin_dir_url );

		new Admin();

		//add plugin menu & plugin css
		//check for debug mode
		$this->suffix = $this->get_script_debug();
		add_action( 'admin_menu', array( $this, 'register_plugin_pages' ) );
		//hide subpages in admin tools menu
		add_action( 'admin_head', array( $this, 'remove_submenu_pages' ), 110 );

		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_js' ) );

	}

	/**
	 * Registers the Plugin stylesheet.
	 *
	 * @param $hook
	 */

	public function register_admin_css( $hook ) {

		//register on plugin  pages only
		if ( in_array( $hook, self::$plugin_pages, FALSE ) ) {

			$url    = ( INSR_DIR . '/assets/css/inpsyde-search-replace' . $this->suffix . '.css' );
			$handle = 'insr-styles';
			wp_register_script( $handle, $url );
			wp_enqueue_style( $handle, $url, array(), FALSE, FALSE );
		}
	}

	/**
	 * Registers the Plugin javascript.
	 *
	 * @param $hook
	 */

	public function register_admin_js( $hook ) {

		//register on plugin pages only
		{
			if ( in_array( $hook, self::$plugin_pages, FALSE ) ) {

				$url    = ( INSR_DIR . '/assets/js/inpsyde-search-replace' . $this->suffix . '.js' );
				$handle = 'insr-js';
				wp_register_script( $handle, $url );
				wp_enqueue_script( $handle, $url, array(), FALSE, FALSE );
			}
		}
	}

	/**
	 *registers admin pages
	 */
	public function register_plugin_pages() {

		//this sets the capability needed to access the menu
		//can be overridden by filter 'insr-capability'
		$cap = apply_filters( 'insr-capability', 'install_plugins' );

		add_submenu_page( 'tools.php', __( 'Backup Database', 'insr' ),
		                  __( 'Search & Replace', 'insr' ), $cap, 'db_backup',
		                  array( $this, 'show_db_backup_page' ) );

		add_submenu_page( 'tools.php', __( 'Replace Domain URL', 'insr' ),
		                  __( 'Replace Domain URL', 'insr' ), $cap, 'replace_domain',
		                  array( $this, 'show_replace_domain_page' ) );

		add_submenu_page( 'tools.php', __( 'Search & Replace', 'insr' ),
		                  __( 'Search & Replace Page', 'insr' ), $cap, 'inpsyde_search_replace',
		                  array( $this, 'show_search_replace_page' ) );

		add_submenu_page( 'tools.php', __( 'SQL Import', 'insr' ),
		                  __( 'SQL Import', 'insr' ), $cap, 'sql_import',
		                  array( $this, 'show_import_page' ) );

		add_submenu_page( 'tools.php', __( 'Credits', 'insr' ),
		                  __( 'Credits', 'insr' ), $cap, 'credits',
		                  array( $this, 'show_credits_page' ) );

	}

	/**
	 * Removes the plugins submenu pages from admin menu.
	 */
	public function remove_submenu_pages() {

		remove_submenu_page( 'tools.php', 'inpsyde_search_replace' );
		remove_submenu_page( 'tools.php', 'sql_import' );
		remove_submenu_page( 'tools.php', 'replace_domain' );
		remove_submenu_page( 'tools.php', 'credits' );
	}

	/**
	 * Callback function for search and replace page
	 */
	public function show_search_replace_page() {

		$search_replace_admin = new SearchReplaceAdmin();
		$search_replace_admin->show_page();
	}

	/**
	 * Callback function for db backup  page
	 */
	public function show_db_backup_page() {

		$export_admin = new DbBackupAdmin();
		$export_admin->show_page();
	}

	/**
	 * Callback function for replace domain page
	 */
	public function show_replace_domain_page() {

		$export_admin = new ReplaceDomainAdmin();
		$export_admin->show_page();
	}

	/**
	 * Callback function for import page
	 */
	public function show_import_page() {

		$import_admin = new SqlImportAdmin();
		$import_admin->show_page();
	}

	/**
	 * Callback function for import page.
	 */
	public function show_credits_page() {

		$import_admin = new CreditsAdmin();
		$import_admin->show_page();
	}

	/**
	 * Checks for script debug mode.
	 *
	 * @return string suffix for css and js files
	 */
	public function get_script_debug() {

		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		return $script_debug ? '' : '.min';
	}

}