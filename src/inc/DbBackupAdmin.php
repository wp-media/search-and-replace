<?php

namespace Inpsyde\SearchReplace\inc;

class DbBackupAdmin extends Admin {

	public function construct() {
	}

	/**
	 *shows the page template
	 */
	public function show_page() {

		if ( array_key_exists( 'action', $_POST )
		     && 'sql_export' === $_POST[ 'action' ]
		     && check_admin_referer( 'sql_export', 'insr_nonce' )
		) {
			$this->handle_sql_export_event();
		}

		require_once( 'templates/db_backup.php' );
	}

	/**
	 * displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'sql_export', 'insr_nonce' );

		$html = '	<input type="hidden" name="action" value="sql_export" />';
		echo $html;
		submit_button( esc_html__( 'Create SQL File', 'insr' ) );

	}

	/**
	 *event handler for click on export sql button
	 */
	private function handle_sql_export_event() {

		$tables = $this->dbm->get_tables();

		$search  = '';
		$replace = '';

		$this->create_backup_file( $search, $replace, $tables );
	}

}