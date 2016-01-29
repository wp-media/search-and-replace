<?php
/**
 * Admin class for the "replace domain" tab in inpsyde search-and-replace plugin.
 */

namespace Inpsyde\SearchReplace\inc;

class ReplaceDomainAdmin extends Admin {

	public function construct() {
	}

	/**
	 *shows the page template
	 */
	public function show_page() {

		if ( array_key_exists( 'action', $_POST )
		     && 'replace_domain' === $_POST[ 'action' ]
		     && check_admin_referer( 'replace_domain', 'insr_nonce' )
		) {
			$this->handle_replace_domain_event();

		}

		require_once( 'templates/replace_domain.php' );
	}

	/**
	 *displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'replace_domain', 'insr_nonce' );

		$html = '<input type="hidden" name="action" value="replace_domain" />';
		echo $html;
		submit_button( esc_attr__( 'Do Search & Replace', 'insr' ) );

	}

	/**
	 *event handler for click on export sql button
	 */
	private function handle_replace_domain_event() {

		$tables = $this->dbm->get_tables();

		$search        = esc_url_raw( $_POST[ 'search' ] );
		$replace       = esc_url_raw( $_POST[ 'replace' ] );
		$new_db_prefix = array_key_exists( 'new_db_prefix', $_POST ) ? esc_attr( $_POST[ 'new_db_prefix' ] ) : '';

		//search field should not be empty
		if ( '' === $replace ) {
			$this->errors->add( 'empty_replace', esc_attr__( 'Replace Field should not be empty.', 'insr' ) );
			$this->display_errors();
			return;
		}

		$this->create_backup_file( $search, $replace, $tables, TRUE, $new_db_prefix );
	}

}