<?php

namespace Inpsyde\SearchAndReplace\Settings;

/**
 * Class AbstractSettingsPage
 *
 * @package Inpsyde\SearchAndReplace\Settings
 */
abstract class AbstractSettingsPage {

	protected $notifications = [
		'errors'  => [],
		'updated' => []
	];

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
	 * @param string $msg
	 */
	public function add_error( $msg ) {

		$this->notifications[ 'error' ][] = (string) $msg;
	}

	/**
	 * @param $msg
	 */
	public function add_updated( $msg ) {

		$this->notifications[ 'updated' ][] = (string) $msg;
	}

	/**
	 * Echoes the content of the $errors array as formatted HTML if it contains error messages.
	 */
	public function render_notifications() {

		foreach ( $this->notifications as $type => $notifications ) {

			if ( count( $notifications ) < 1 ) {
				continue;
			}
			?>
			<div class="<?= esc_attr( $type ) ?> notice is-dismissible">
				<ul>
					<?php foreach ( $notifications as $msg ) : ?>
						<li><?= esc_html( $msg ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		}
	}

	/**
	 * Displays the html for the submit button.
	 *
	 * @param string $name
	 */
	public function show_submit_button( $name = 'submit' ) {

		printf(
			'<input type="hidden" name="action" value="%s" />',
			esc_attr( $this->get_slug() )
		);
		submit_button( $this->get_submit_button_title(), 'primary', $name );
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
