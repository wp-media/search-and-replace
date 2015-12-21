<?php
/**
 *
 */

namespace Inpsyde\SearchReplace\inc;

class SqlExportAdmin extends Admin {

	function construct() {

		parent::__construct();
	}

	/**
	 *shows the page template
	 */
	public function show_page() {

		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "sql_export" ) {
			$this->handle_sql_export_event();

		}

		require_once( 'templates/sql_export.php' );
	}

	/**
	 *displays the site url, strips http: or https: before display
	 */
	protected function show_site_url() {

		$url          = get_site_url();
		$stripped_url = substr( $url, strpos( $url, ':' ) + 1 );
		echo $stripped_url;

	}

	/**
	 *displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'sql_export', 'insr_nonce' );

		$html = '	<input type="hidden" name="action" value="sql_export" />';
		echo $html;
		submit_button( __( 'Create SQL File', 'insr' ) );

	}

	/**
	 *event handler for click on export sql button
	 */
	private function handle_sql_export_event() {

		$tables = $this->dbm->get_tables();
		if ( isset ( $_POST[ 'change_url' ] ) ) {
			$search  = $_POST[ 'search' ];
			$replace = $_POST[ 'replace' ];

			//replace field should not be empty
			if ( $replace =='') {
				$this->errors->add( 'empty_replace', __( 'Replace Field should not be empty.', 'insr' ) );
				$this->display_errors();
				return;
			}
		//if change_url is not set we set $search and $replace to empty strings.
		} else {
			$search  = '';
			$replace = '';
		}
		$this->create_backup_file( $search, $replace, $tables );
	}

}