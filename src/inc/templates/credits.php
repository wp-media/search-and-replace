<?php
/**
 * Template for displaying sql export page
 */
// Prevent direct access.
if ( ! defined( 'INSR_DIR' ) ) {
	echo "Hi there!  I'm just a part of plugin, not much I can do when called directly.";
	exit;
}
?>
<div class="wrap">

	<h1 id="title"><?php esc_html_e( 'Search & Replace', 'insr' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab" href="<?php
		echo admin_url() ?>tools.php?page=db_backup"><?php esc_html_e( 'Backup Database', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php
		echo admin_url() ?>tools.php?page=replace_domain"><?php esc_html_e( 'Replace Domain/URL', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php
		echo admin_url() ?>tools.php?page=inpsyde_search_replace"><?php esc_html_e( 'Search and Replace', 'insr' ); ?></a>
		<a class="nav-tab" href="<?php
		echo admin_url() ?>tools.php?page=sql_import"><?php esc_html_e( 'Import SQL file', 'insr' ); ?></a>
		<a class="nav-tab nav-tab-active" href="<?php
		echo admin_url() ?>tools.php?page=credits"><?php esc_html_e( 'Credits', 'insr' ); ?></a>
	</h2>

	<h2><?php esc_html_e( 'Hey nice to have you here!', 'insr' ); ?></h2>
	<p><?php printf(
			__( 'Search and Replace is refactored in 2015 by <a href="%1$s">Inpsyde GmbH</a>, maintained since 2006 and based on the original from <a href="%2$s">Mark Cunningham</a>.', 'insr' ),
			'http://inpsyde.com/',
			'http://thedeadone.net'
		); ?></p>

	<h2><?php esc_html_e( 'You rock! contribute the plugin.', 'insr' ); ?></h2>
	<p><?php printf(
			__( 'You can contribute the Plugin go to the repository on <a href="%s">github</a> making changes, create issues or submitting changes.', 'insr' ),
			'https://github.com/inpsyde/search-and-replace/'
		); ?></p>

	<h2><?php esc_html_e( 'We are Inpsyde', 'insr' ); ?></h2>
	<p><?php esc_html_e( 'Inpsyde has developed enterprise solutions with the world’s most popular open-source CMS since it was a kitten. Still do, inconvincible convinced.', 'insr' ); ?></p>
	<p><?php printf(
			__( 'Inpsyde is a WordPress <a href="%1$s">VIP Service Partner</a> and <a href="%2$s">WooCommerce Expert</a>.', 'insr' ),
			'https://vip.wordpress.com/partner/inpsyde/',
			'https://www.woothemes.com/experts/inpsyde-gmbh/'
		); ?></p>
	<p><?php printf(
			__( 'Look at our other <a href="%s">free WordPress plugins</a>.', 'insr' ),
			'https://profiles.wordpress.org/inpsyde/#content-plugins'
		); ?></p>


	<h2><?php esc_html_e( 'Working at Inpsyde', 'insr' ); ?></h2>
	<p><?php esc_html_e( 'The biggest WordPress enterprise in Europe we’re dynamically growing and constantly looking for new employees. So do you want to shape WordPress in an interesting and exciting working environment? Here we are!', 'insr' ); ?> </p>
	<p><?php esc_html_e( 'At the moment we’re looking for developers for WordPress based products and services. If you’re not a developer and want to be part of us, we’d be happy to recieve your unsolicited application. At Inpsyde you can expect an open, modern and lively company culture:', 'insr' ); ?> </p>
	<ol>
		<li><?php esc_html_e( 'challenging and exciting projects', 'insr' ); ?></li>
		<li><?php esc_html_e( 'flexible working hours in remote office', 'insr' ); ?></li>
		<li><?php esc_html_e( 'deliberately flat hierarchies and short decision paths', 'insr' ); ?></li>
		<li><?php esc_html_e( 'a wide variety of tasks', 'insr' ); ?></li>
		<li><?php esc_html_e( 'freedom for personal development and responsible, self-reliant action', 'insr' ); ?></li>

	</ol>
	<p><?php printf(
			__( 'If you love open source and especially WordPress, if you love to organize your working days by yourself and want to use your pragmatic problem-solving skills and result-oriented work methods: <a href="%s">join our team</a>!', 'insr' ),
			'http://inpsyde.com/#jobs'
		); ?></p>

