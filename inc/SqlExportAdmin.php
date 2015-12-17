<?php
/**
 *
 */

namespace Inpsyde\SearchReplace\inc;

class SqlExportAdmin extends Admin {

	function construct(){
		parent::__construct();
	}

	/**
	 *
	 */
	public function show_page() {
		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "sql_export" ) {
			$this->handle_sql_export_event();

		}

		require_once( 'templates/sql_export.php' );
	}

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

	private function handle_sql_export_event() {
		$tables = $this->dbm->get_tables();
		$this->create_backup_file($tables);
	}

}