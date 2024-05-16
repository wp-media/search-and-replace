<?php
/**
 * Template for displaying sql export page
 */

// Prevent direct access.
if ( ! defined( 'SEARCH_REPLACE_BASEDIR' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
?>

<h2><?php esc_html_e( 'Hey nice to have you here!', 'search-and-replace' ); ?></h2>
<p>
	<?php printf(
		wp_kses(
			__(
				'Search and Replace is onwend since 2024 by <a href="%3$s">WP Media</a> and refactored in 2015 by <a href="%1$s">Syde GmbH</a>.It is based on the original from <a href="%2$s">Mark Cunningham</a>.',
				'search-and-replace'
			),
			[
				'a' => [
					'href' => true,
				],
			]
		),
		'https://syde.com/',
		'https://thedeadone.net',
        'https://wp-media.me'
	);
	?>
</p>

<h2><?php esc_html_e( 'You rock! contribute the plugin.', 'search-and-replace' ); ?></h2>
<p>
	<?php printf(
		wp_kses(
			__(
				'You can contribute the Plugin go to the repository on <a href="%s">github</a> making changes, creating issues, or submitting changes.',
				'search-and-replace'
			),
			[
				'a' => [
					'href' => true,
				],
			]
		),
		'https://github.com/wp-media/search-and-replace/'
	);
	?>
</p>
