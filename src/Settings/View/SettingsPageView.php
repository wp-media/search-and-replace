<?php

namespace Inpsyde\SearchAndReplace\Settings\View;

use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;

/**
 * @package Inpsyde\SearchAndReplace\Page
 */
class SettingsPageView implements SettingsPageViewInterface {

	/**
	 * {@inheritdoc}
	 */
	public function render( array $pages = [] ) {

		$url          = admin_url( 'tools.php' );
		$current_page = isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : key( $pages );

		?>
		<div class="wrap">';
			<h1 class="settings__headline">' . esc_html__( 'Search & Replace', 'search-and-replace' ) . '</h1>';

			<div class="inpsyde-tabs">
				<div class="inpsyde-tab__navigation">
					<?php
					foreach ( $pages as $slug => $page ) :
						$class = $current_page === $slug ? 'ui-tabs-active' : '';
						printf(
							'<li class="inpsyde-tab__navigation-item %1$s"><a href="%2$s">%3$s</a></li>',
							esc_attr( $class ),
							add_query_arg( 'page', $slug, $url ),
							$page->get_page_title()
						);
					endforeach;
					unset( $page );
					?>
				</div>
				<div class="inpsyde-tab__content">
					<?php
					// Set the current page.
					/** @var SettingsPageInterface $page */
					$page = $pages[ $current_page ];
					$page->display_errors();
					$page->render();
					?>
				</div>
				<img
					src="<?= esc_url( $this->config->get( 'assets.img.url' ) . 'inpsyde.svg' ); ?>"
					alt="Inpsyde GmbH"
					width="150"
					height="47"
					class="inpsyde-logo__image"
				/>
			</div>
		</div>
		<?php
	}

}
