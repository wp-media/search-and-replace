<?php

namespace Inpsyde\SearchReplace\inc;

class Admin {

	/**
	 * @var DatabaseManager
	 * stores instance of DatabaseManager
	 */
	protected $dbm;

	public function __construct( $dbm ) {

		if ( ! $dbm instanceof DatabaseManager ) {
			throw new \InvalidArgumentException ( "Class Replace needs Object of Type DatabaseManager as Parameter" );

		}

		add_action( 'admin_menu', array( $this, 'register_plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_css' ) );

		$this->dbm = $dbm;

	}

	/**
	 *registers the Plugin stylesheet
	 */

	public function register_admin_css( $hook ) {

		//register on plugin admin page only
		if ( $hook == 'tools_page_inpsyde_search_replace' ) {

			$url    = ( WP_PLUGIN_URL . '/SearchReplace/css/inpsyde-search-replace.css' );
			$handle = "insr-styles";
			wp_register_script( $handle, $url );
			wp_enqueue_style( $handle, $url, array(), FALSE, FALSE );
		}
	}

	/**
	 *registers admin page
	 */
	public function register_plugin_menu() {

		//this sets the capability needed to access the menu
		//can be overridden by filter 'insr-capability'
		$cap = apply_filters( 'insr-capability', 'install_plugins' );

		$page = add_submenu_page( 'tools.php', __( 'Inpsyde Search & Replace', 'insr' ),
		                          __( 'Inpsyde Search & Replace', 'insr' ), $cap, 'inpsyde_search_replace',
		                          array( $this, 'show_dashboard' ) );

	}

	/**
	 *callback function for menu item
	 */
	public function show_dashboard() {

		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "search_replace" ) {
			$this->replace();

		}

		require_once( 'templates/dashboard.php' );
	}

	/**
	 *prints a select with all the tables and their sizes
	 */
	protected function show_table_list() {

		$tables      = $this->dbm->get_tables();
		$sizes       = $this->dbm->get_sizes();
		$table_count = count( $tables );

		//adjust height of select according to table count, but max 20 rows
		$select_rows = $table_count < 20 ? $table_count : 20;

		echo '<select id="select_tables" name="select_tables[]" multiple="multiple"  size = "' . $select_rows . '">';
		foreach ( $tables as $table ) {
			$table_size = isset ( $sizes[ $table ] ) ? $sizes[ $table ] : '';
			echo "<option value='$table'>$table $table_size</option>option>";

		}

	}

	protected function show_submit_button() {

		wp_nonce_field( 'do_search_replace', 'insr_nonce' );
		$value = translate( "Do Search / Replace", "insr" );

		$html = '	<input type="hidden" name="action" value="search_replace" /><input id ="insr_submit"type="submit" value="' . $value . ' "class="button" />';

		echo $html;
	}

	protected function replace() {

		echo( "Replace was called" );
	}

}