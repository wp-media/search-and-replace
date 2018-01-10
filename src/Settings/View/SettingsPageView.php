<?php

namespace Inpsyde\SearchAndReplace\Settings\View;

use Inpsyde\SearchAndReplace\Core\PluginConfig;
use Inpsyde\SearchAndReplace\Http\Request;
use Inpsyde\SearchAndReplace\Settings\SettingsPageInterface;
use Inpsyde\SearchAndReplace\Settings\UpdateAwareSettingsPage;

/**
 * @package Inpsyde\SearchAndReplace\Page
 */
class SettingsPageView implements SettingsPageViewInterface {

	/**
	 * @var PluginConfig
	 */
	protected $config;

	/**
	 * SettingsPageView constructor.
	 *
	 * @param PluginConfig $config
	 */
	public function __construct( PluginConfig $config ) {

		$this->config = $config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $pages = [], Request $request ) {

		$url          = admin_url( 'tools.php' );
		$current_page = $request->query()
			->has( 'page' )
			? $request->query()
				->get( 'page' )
			: key( $pages );

		$pages = array_filter(
			$pages,
			function ( SettingsPageInterface $page ) use ( $request ) {

				return ( $page instanceof UpdateAwareSettingsPage && ! $page->auth()->is_allowed( $request ) ) || TRUE;
			}
		);

		?>
		<div class="wrap">
			<h1 class="settings__headline"><?php esc_html_e( 'Search & Replace', 'search-and-replace' ) ?></h1>

			<div class="inpsyde-tabs">
				<div class="inpsyde-tab__navigation">
					<?php
					/**
					 * @var string                $slug
					 * @var SettingsPageInterface $page
					 */
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
					$page->render_notifications();
					$page->render( $request );
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
