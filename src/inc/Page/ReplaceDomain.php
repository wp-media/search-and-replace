<?php

namespace Inpsyde\SearchReplace\Page;

use Inpsyde\SearchReplace\Database\Exporter;
use Inpsyde\searchReplace\Database\Manager;

/**
 * Class ReplaceDomain
 *
 * @package Inpsyde\SearchReplace\inc\Page
 */
class ReplaceDomain extends AbstractPage implements PageInterface {

	/**
	 * @var Exporter
	 */
	private $dbe;

	/**
	 * @var Manager
	 */
	private $dbm;

	/**
	 * ReplaceDomain constructor.
	 *
	 * @param Manager  $dbm
	 * @param Exporter $dbe
	 */
	public function __construct( Manager $dbm, Exporter $dbe ) {
		$this->dbm = $dbm;
		$this->dbe = $dbe;
	}

	public function save() {

		$search        = esc_url_raw( filter_input( INPUT_POST, 'search' ) );
		$replace       = esc_url_raw( filter_input( INPUT_POST, 'replace' ) );
		$new_db_prefix = esc_attr( filter_input( INPUT_POST, 'new_db_prefix' ) );

		//search field should not be empty
		if ( '' === $replace ) {
			$this->errors->add(
				'empty_replace', esc_attr__( 'Replace Field should not be empty.', 'search-and-replace' )
			);
			$this->display_errors();

			return;
		}

		$this->dbe->create_backup_file( $search, $replace, array(), TRUE, $new_db_prefix );
	}

	/**
	 * shows the page template
	 */
	public function render() {

		require_once(  __DIR__ . '/../templates/replace_domain.php' );
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return __( 'Do Replace Domain/Url', 'search-and-replace' );
	}

	/**
	 * @return string
	 */
	public function get_menu_title() {

		return __( 'Replace Domain URL', 'search-and-replace' );
	}

	/**
	 * @return string
	 */
	public function get_page_title() {

		return __( 'Replace Domain URL', 'search-and-replace' );
	}

}