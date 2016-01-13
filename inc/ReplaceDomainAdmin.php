<?php
/**
 *
 */

namespace Inpsyde\SearchReplace\inc;

class ReplaceDomainAdmin extends Admin {

	function construct() {

		parent::__construct();
	}

	/**
	 *shows the page template
	 */
	public function show_page() {

		if ( isset ( $_POST[ 'action' ] ) && $_POST[ 'action' ] == "replace_domain" ) {
			$this->handle_replace_domain_event();

		}

		require_once( 'templates/replace_domain.php' );
	}

	/**
	 *displays the site url, strips http:// or https:// before display
	 */
	protected function show_site_url() {

		$url          = get_site_url();
		$stripped_url = substr( $url, strpos( $url, '/' ) + 2 );
		echo $stripped_url;

	}

	/**
	 *displays the html for the submit button
	 */
	protected function show_submit_button() {

		wp_nonce_field( 'replace_domain', 'insr_nonce' );

		$html = '	<input type="hidden" name="action" value="replace_domain" />';
		echo $html;
		submit_button( __( 'Create SQL File', 'insr' ) );

	}

	/**
	 *event handler for click on export sql button
	 */
	private function handle_replace_domain_event() {

		$tables = $this->dbm->get_tables();

			$search  = $_POST[ 'search' ];
			$replace = $_POST[ 'replace' ];

			//search field should not be empty
			if ( $replace == '' ) {
				$this->errors->add( 'empty_replace', __( 'Replace Field should not be empty.', 'insr' ) );
				$this->display_errors();

				return;

		}
		$this->create_backup_file( $search, $replace, $tables );
	}

}