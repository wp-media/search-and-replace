<?php

namespace Inpsyde\SearchReplace\Page;

/**
 * Class AbstractPage
 *
 * @package Inpsyde\SearchReplace\Page
 */
abstract class AbstractPage {

	/**
	 * Returns the translated title for the page.
	 *
	 * @return string
	 */
	abstract public function get_page_title();

	/**
	 * By default "Search & Replace". Can be overwritten in child-classes.
	 * 
	 * @return string
	 */
	public function get_menu_title() {

		return esc_html__( 'Search & Replace', 'search-and-replace' );
	}

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @param string $msg
	 */
	public function add_error( $msg ) {

		$this->errors[] = (string) $msg;
	}

	/**
	 * Echoes the content of the $errors array as formatted HTML if it contains error messages.
	 */
	public function display_errors() {

		if ( count( $this->errors ) < 1 ) {
			return;
		}

		$html = '<div class="error notice is-dismissible">';
		$html .= sprintf( '<strong>%s</strong>', esc_html__( 'Errors:', 'search-and-replace' ) );
		$html .= '<ul>';
		foreach ( $this->errors as $error ) :
			$html .= '<li>' . esc_html( $error ) . '</li>';
		endforeach;
		$html .= '</ul>';
		$html .= '</div>';

		echo $html;
	}

	/**
	 *displays the html for the submit button
	 */
	public function show_submit_button() {

		echo '<input type="hidden" name="action" value="' . $this->get_slug() . '" />';
		submit_button( $this->get_submit_button_title() );
		wp_nonce_field( 'replace_domain', 'insr_nonce' );
	}

	/**
	 * @return string
	 */
	protected function get_submit_button_title() {

		return esc_html__( 'Submit', 'search-replace' );
	}

	/**
	 * @return string
	 */
	public function get_slug() {

		return sanitize_title_with_dashes( $this->get_page_title() );
	}
}