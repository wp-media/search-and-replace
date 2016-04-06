<?php

namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database;

/**
 * Class ReplaceDomain
 *
 * @package Inpsyde\SearchReplace\inc\Page
 */
class ReplaceDomain extends AbstractPage implements PageInterface {

	/**
	 * @var \Inpsyde\SearchReplace\Database\Exporter
	 */
	private $dbe;

	/**
	 * @var Manager
	 */
	private $dbm;

	/**
	 * ReplaceDomain constructor.
	 *
	 * @param \Inpsyde\SearchReplace\Database\Manager|\Inpsyde\SearchReplace\Page\Manager $dbm
	 * @param \Inpsyde\SearchReplace\Database\Exporter                                    $dbe
	 */
	public function __construct( Database\Manager $dbm, Database\Exporter $dbe ) {

		$this->dbm = $dbm;
		$this->dbe = $dbe;
	}

	public function save() {

		$search        = esc_url_raw( filter_input( INPUT_POST, 'search' ) );
		$replace       = esc_url_raw( filter_input( INPUT_POST, 'replace' ) );
		$new_db_prefix = esc_attr( filter_input( INPUT_POST, 'new_db_prefix' ) );

		//search field should not be empty
		if ( '' === $replace ) {
			$this->add_error( esc_html__( 'Replace Field should not be empty.', 'search-and-replace' ) );

			return;
		}

		$this->dbe->create_backup_file( $search, $replace, array(), TRUE, $new_db_prefix );
	}

	/**
	 * shows the page template
	 */
	public function render() {

		require_once( __DIR__ . '/../templates/replace_domain.php' );
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Do Replace Domain/Url', 'search-and-replace' );
	}

	/**
	 * @return string
	 */
	public function get_menu_title() {

		return esc_html__( 'Replace Domain URL', 'search-and-replace' );
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return esc_html__( 'Replace Domain URL', 'search-and-replace' );
	}

}