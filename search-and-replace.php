<?php
/**
 * Plugin Name: Search &amp; Replace
 * Text Domain: searchandreplace
 * Domain Path: /languages
 * Plugin URI:  http://wordpress.org/plugins/search-and-replace/
 * Description: A simple search to find strings in your database and replace those strings.
 * Author:      Frank Bültge
 * Author URI:  http://bueltge.de
 * Version:     2.7.1
 * License:     GPLv2+
 * Donate URI:
 *
 *
 * License:
 * ==============================================================================
 * Copyright 2009 - 2014 Frank Bueltge  (email : frank@bueltge.de)
 * Hints, maintain updates in 2014 from Ron Guerin <ron@vnetworx.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

// plugin definitions
define( 'FB_SAR_BASENAME', plugin_basename( __FILE__ ) );
define( 'FB_SAR_BASEDIR', dirname( plugin_basename( __FILE__ ) ) );
define( 'FB_SAR_TEXTDOMAIN', 'searchandreplace' );

function searchandreplace_textdomain() {

	load_plugin_textdomain( FB_SAR_TEXTDOMAIN, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Add action link(s) to plugins page
 *
 * @return void
 */
function searchandreplace_on_load() {

	add_filter( 'plugin_action_links_' . FB_SAR_BASENAME, 'searchandreplace_filter_plugin_meta', 10, 2 );
}

function searchandreplace_filter_plugin_meta( $links, $file ) {

	if ( empty( $links ) ) {
		return NULL;
	}

	/* create link */
	if ( $file == FB_SAR_BASENAME ) {
		array_unshift(
			$links,
			sprintf( '<a href="tools.php?page=%s">%s</a>', FB_SAR_BASENAME, __( 'Settings' ) )
		);
	}

	return $links;
}

/**
 * Settings in plugin-admin-page
 *
 * @return void
 */
function searchandreplace_add_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	add_management_page(
		__( 'Search &amp; Replace', FB_SAR_TEXTDOMAIN ), __( 'Search &amp; Replace', FB_SAR_TEXTDOMAIN ),
		'manage_options', FB_SAR_BASENAME, 'searchandreplace_page', ''
	);
	add_action( 'load-plugins.php', 'searchandreplace_on_load' );
}

add_action( 'plugins_loaded', 'searchandreplace_init' );
/**
 * Init to WP
 *
 * @return void
 */
function searchandreplace_init() {

	add_action( 'admin_init', 'searchandreplace_textdomain' );
	add_action( 'admin_menu', 'searchandreplace_add_settings_page' );
	add_action( 'admin_print_scripts', 'searchandreplace_add_js_head' );
}

/* this does the important stuff! */
function searchandreplace_doit(
	$search_text,
	$replace_text,
	$sall = TRUE,
	$content = TRUE,
	$guid = TRUE,
	$id = TRUE,
	$title = TRUE,
	$excerpt = TRUE,
	$meta_value = TRUE,
	$comment_content = TRUE,
	$comment_author = TRUE,
	$comment_author_email = TRUE,
	$comment_author_url = TRUE,
	$comment_count = TRUE,
	$cat_description = TRUE,
	$tag = TRUE,
	$user_id = TRUE,
	$user_login = TRUE,
	$signups = TRUE
) {

	global $wpdb;

	$myecho = '';
	// slug string
	$search_slug  = strtolower( $search_text );
	$replace_slug = strtolower( $replace_text );

	if ( ! $sall && ! $content && ! $id && ! $guid && ! $title && ! $excerpt && ! $meta_value
		&&
		! $comment_content
		&& ! $comment_author
		&& ! $comment_author_email
		&& ! $comment_author_url
		&& ! $comment_count
		&&
		! $cat_description
		&& ! $tag
		&& ! $user_id
		&& ! $user_login
		&&
		! $signups
	) {
		return '<div class="error"><p><strong>' . __(
			'Nothing (checkbox) selected to modify!', FB_SAR_TEXTDOMAIN
		) . '</strong></p></div><br class="clear" />';
	}

	// search at all
	if ( 'sall' === $sall ) {
		$myecho .= "\n" . '<li>' . __( 'Searching all', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_sall( $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";
	}

	// search/replace at all
	if ( 'srall' === $sall ) {
		$myecho .= "\n" . '<li>' . __( 'Searching & replacing all', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_sall( $search_text, $replace_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";
	}

	// post content
	if ( $content ) {
		$myecho .= "\n" . '<li>' . __( 'Searching post content', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'post_content', 'posts', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET post_content = ";
		$query .= "REPLACE(post_content, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// post id
	if ( $id ) {
		$myecho .= "\n" . __( 'Searching ID', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'ID', 'posts', $search_text );
		$myecho .= searchandreplace_results( 'post_parent', 'posts', $search_text );
		$myecho .= searchandreplace_results( 'post_id', 'postmeta', $search_text );
		$myecho .= searchandreplace_results( 'object_id', 'term_relationships', $search_text );
		$myecho .= searchandreplace_results( 'comment_post_ID', 'comments', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET ID = ";
		$query .= "REPLACE(ID, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET post_parent = ";
		$query .= "REPLACE(post_parent, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->postmeta ";
		$query .= "SET post_id = ";
		$query .= "REPLACE(post_id, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->term_relationships ";
		$query .= "SET object_id = ";
		$query .= "REPLACE(object_id, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->comments ";
		$query .= "SET comment_post_ID = ";
		$query .= "REPLACE(comment_post_ID, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// post guid
	if ( $guid ) {
		$myecho .= "\n" . '<li>' . __(
				'Searching <abbr title=\"Global Unique Identifier\">GUID</abbr>', FB_SAR_TEXTDOMAIN
			) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'guid', 'posts', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET guid = ";
		$query .= "REPLACE(guid, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// post title
	if ( $title ) {
		$myecho .= "\n" . '<li>' . __( 'Searching Title', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'post_title', 'posts', $search_text );
		$myecho .= searchandreplace_results( 'post_name', 'posts', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET post_title = ";
		$query .= "REPLACE(post_title, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET post_name = ";
		$query .= "REPLACE(post_name, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// post excerpt
	if ( $excerpt ) {
		$myecho .= "\n" . '<li>' . __( 'Searching post excerpts', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'post_excerpt', 'posts', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET post_excerpt = ";
		$query .= "REPLACE(post_excerpt, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// meta_value
	if ( $meta_value ) {
		$myecho .= "\n" . '<li>' . __( 'Searching metadata', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'meta_value', 'postmeta', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->postmeta ";
		$query .= "SET meta_value = ";
		$query .= "REPLACE(meta_value, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// comment content
	if ( $comment_content ) {
		$myecho .= "\n" . '<li>' . __( 'Searching comments text', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'comment_content', 'comments', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->comments ";
		$query .= "SET comment_content = ";
		$query .= "REPLACE(comment_content, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// comment_author
	if ( $comment_author ) {
		$myecho .= "\n" . '<li>' . __( 'Searching comments authors', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'comment_author', 'comments', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->comments ";
		$query .= "SET comment_author = ";
		$query .= "REPLACE(comment_author, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// comment_author_email
	if ( $comment_author_email ) {
		$myecho .= "\n" . '<li>' . __( 'Searching comments authors e-mails', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'comment_author_email', 'comments', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->comments ";
		$query .= "SET comment_author_email = ";
		$query .= "REPLACE(comment_author_email, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// comment_author_url
	if ( $comment_author_url ) {
		$myecho .= "\n" . '<li>' . __( 'Searching comments authors URLs', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'comment_author_url', 'comments', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->comments ";
		$query .= "SET comment_author_url = ";
		$query .= "REPLACE(comment_author_url, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// comment_count
	if ( $comment_count ) {
		$myecho .= "\n" . '<li>' . __( 'Searching comment counts', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'comment_count', 'posts', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET comment_count = ";
		$query .= "REPLACE(comment_count, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// category description
	if ( $cat_description ) {
		$myecho .= "\n" . '<li>' . __( 'Searching category descriptions', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'description', 'term_taxonomy', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->term_taxonomy ";
		$query .= "SET description = ";
		$query .= "REPLACE(description, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	// tags and category
	if ( $tag ) {
		$myecho .= "\n" . '<li>' . __( 'Searching tags', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'name', 'terms', $search_text );
		$myecho .= searchandreplace_results( 'slug', 'terms', $search_slug );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->terms ";
		$query .= "SET name = ";
		$query .= "REPLACE(name, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->terms ";
		$query .= "SET slug = ";
		$query .= "REPLACE(slug, \"$search_slug\", \"$replace_slug\") ";
		$wpdb->get_results( $query );
	}

	// user_id
	if ( $user_id ) {
		$myecho .= "\n" . '<li>' . __( 'Searching user IDs', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'ID', 'users', $search_text );
		$myecho .= searchandreplace_results( 'user_id', 'usermeta', $search_slug );
		$myecho .= searchandreplace_results( 'post_author', 'posts', $search_slug );
		$myecho .= searchandreplace_results( 'user_id', 'comments', $search_slug );
		$myecho .= searchandreplace_results( 'link_owner', 'links', $search_slug );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->users ";
		$query .= "SET ID = ";
		$query .= "REPLACE(ID, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->usermeta ";
		$query .= "SET user_id = ";
		$query .= "REPLACE(user_id, \"$search_slug\", \"$replace_slug\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->posts ";
		$query .= "SET post_author = ";
		$query .= "REPLACE(post_author, \"$search_slug\", \"$replace_slug\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->comments ";
		$query .= "SET user_id = ";
		$query .= "REPLACE(user_id, \"$search_slug\", \"$replace_slug\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->links ";
		$query .= "SET link_owner = ";
		$query .= "REPLACE(link_owner, \"$search_slug\", \"$replace_slug\") ";
		$wpdb->get_results( $query );
	}

	// user_login
	if ( $user_login ) {
		$myecho .= "\n" . '<li>' . __( 'Searching user logins', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'user_login', 'users', $search_text );
		$myecho .= searchandreplace_results( 'user_nicename', 'users', $search_slug );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->users ";
		$query .= "SET user_login = ";
		$query .= "REPLACE(user_login, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );

		$query = "UPDATE $wpdb->users ";
		$query .= "SET user_nicename = ";
		$query .= "REPLACE(user_nicename, \"$search_slug\", \"$replace_slug\") ";
		$wpdb->get_results( $query );
	}

	// signups on WP multisite
	if ( $signups ) {
		$myecho .= "\n" . '<li>' . __( 'Searching signups', FB_SAR_TEXTDOMAIN ) . ' ...';

		$myecho .= "\n" . '<ul>' . "\n";
		$myecho .= searchandreplace_results( 'user_login', 'signups', $search_text );
		$myecho .= "\n" . '</ul>' . "\n" . '</li>' . "\n";

		$query = "UPDATE $wpdb->signups ";
		$query .= "SET user_login = ";
		$query .= "REPLACE(user_login, \"$search_text\", \"$replace_text\") ";
		$wpdb->get_results( $query );
	}

	$echo = '<div class="updated fade">' . "\n" . '<ul>';
	$echo .= $myecho;
	$echo .= "\n" . '</ul>' . "\n" . '</div><br class="clear"/>' . "\n";

	return $echo;
}

/**
 * Get results
 *
 * @param $field
 * @param $table
 * @param $search_text
 *
 * @return string
 */
function searchandreplace_results( $field, $table, $search_text ) {

	global $wpdb;

	$myecho = '';

	$myecho .= "\n" . '<li>';
	$results = "SELECT $field FROM " . $wpdb->$table . " WHERE $field LIKE \"%$search_text%\"";
	$myecho .= __( '... in table', FB_SAR_TEXTDOMAIN ) . ' ';
	$myecho .= '<code>' . $table . '</code>,' . ' Field: <code>' . $field . '</code>: ';
	$results       = $wpdb->get_results( $results, ARRAY_A );
	$total_results = $wpdb->num_rows;

	if ( $results === FALSE ) {
		$myecho .= __( 'The query could not be executed:', FB_SAR_TEXTDOMAIN ) . ' ' . $wpdb->print_error();
	} else {

		if ( $total_results == 0 ) {
			$myecho .= ' - <strong>' . $total_results . '</strong> ';
		} else {
			foreach ( $results as $result ) {
				//echo $row[$field] . "\n";
				$myecho .= '|';
			}
			$myecho .= ' - <strong>' . $total_results . '</strong> ';
		}
		$myecho .= __( 'entries found.', FB_SAR_TEXTDOMAIN );
		$myecho .= '</li>' . "\n";
	}

	return $myecho;
}

function searchandreplace_sall( $search_text, $replace_text = FALSE ) {

	global $wpdb;

	if ( ! defined( 'DB_COLLATE' ) || '' === DB_COLLATE ) {
		$db_collate = 'utf8mb4_unicode_ci';
	} elseif ( 'utf8' === DB_CHARSET ) {
		$db_collate = 'utf8mb4_unicode_ci';
	} else {
		$db_collate = DB_COLLATE;
	}

	if ( empty( $wpdb->dbname ) ) {
		$wpdb->dbname = DB_NAME;
	}

	$search_text = esc_sql( $search_text ); # this appears to be escaped already
	$search_text = stripslashes_deep( $search_text );

	if ( $replace_text ) {
		$replace_text = esc_sql( $replace_text );
	} # this appears to be escaped already
	$replace_text = stripslashes_deep( $replace_text );

	$result_in_tables = 0;

	$myecho = '
	<script language="JavaScript">
		var table_id = new Array();

		function hide_all() {
			for(i=0;i<table_id.length;i++){
				document.getElementById(table_id[i]).style.display = \'none\';
			}
		}

		function show_all() {
			for(i=0;i<table_id.length;i++){
				document.getElementById(table_id[i]).style.display = \'block\';
			}
		}

		function toggle(id) {
			if (get_style(id,\'display\') == \'block\') {
				document.getElementById(id).style.display = \'none\';
			} else {
				document.getElementById(id).style.display = \'block\';
			}
		}

		function get_style(el,styleProp) {
			var x = document.getElementById(el);
			if (x.currentStyle)
				var y = x.currentStyle[styleProp];
			else if (window.getComputedStyle)
				var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
			return y;
		}
	</script>';

	$myecho .= '<p><a href="javascript:hide_all()">' . __( 'Collapse All', FB_SAR_TEXTDOMAIN ) . '</a>
		 <a href="javascript:show_all()">' . __( 'Expand All', FB_SAR_TEXTDOMAIN ) . '</a></p>';
	$myecho .= '<p>' . __( 'Results for', FB_SAR_TEXTDOMAIN ) . ': <code>' . stripslashes(
			$search_text
		) . '</code></p><p>' . __(
			'Please note search text may appear (and be replaced) more than one time in each row.', FB_SAR_TEXTDOMAIN
		) . '</p>';

	$sql    = 'SHOW TABLES';
	$tables = $wpdb->get_results( $sql, ARRAY_A );

	$replace_sql = '';

	for ( $i = 0; $i < count( $tables ); $i ++ ) {
		//@abstract query building of each table
		if ( $wpdb->get_var( "SELECT COUNT(*) FROM " . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] ) > 0 ) {
			//@abstract get the table data type information
			$sql    = 'desc ' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ];
			$column = $wpdb->get_results( $sql, ARRAY_A );

			$search_sql = 'SELECT * FROM ' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . ' WHERE ';
			// replace string
			if ( $replace_text ) {
				$replace_sql = 'UPDATE ' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . ' SET ';
			}
			$no_varchar_field = 0;

			for ( $j = 0; $j < count( $column ); $j ++ ) {

				if ( $no_varchar_field != 0 ) {
					$search_sql .= 'or ';

					//if ( $replace_text ) {
					//	$replace_sql .= ', ';
					//}

				}

				//  COLLATE utf8_bin  for search case sensitive
				$search_sql .= '`' . $column[ $j ][ 'Field' ] . '` like \'%' . $search_text . '%\' COLLATE ' . $db_collate . ' ';

				if ( $replace_text ) {

					$search_result = $wpdb->get_results( $search_sql, ARRAY_A );

					// Loop about the result and check for serialized data
					// Unset serialized data, no changes
					foreach ( $search_result as $key => $values ) {

						foreach ( $values as $field => $value ) {
							if ( is_serialized( $value ) ) {
								/*
								$value = @unserialize( $value );
								$value = json_decode(
									str_replace( $search_text, $replace_text, json_encode( $value ) )
								);
								$value = serialize( $value );
								*/

								if ( isset( $column[ $j ][ 'Field' ] ) && $field === $column[ $j ][ 'Field' ] ) {
									unset( $column[ $j ][ 'Field' ] );
								}
							}
						}

					}

					if ( ! empty( $column[ $j ][ 'Field' ] ) ) {
						$replace_sql .= $column[ $j ][ 'Field' ] . ' = ';
						// Note that when searching for text to replace, MySQL uses case-sensitive match to perform search for string to be replaced.
						$replace_sql .= 'REPLACE(' . $column[ $j ][ 'Field' ] . ', "' . $search_text . '", "' . $replace_text . '"), ';
					}
				}

				$no_varchar_field ++;
			}

			if ( $no_varchar_field > 0 ) {

				$search_result = $wpdb->get_results( $search_sql, ARRAY_A );
				if ( $replace_text ) {
					$replace_sql = rtrim( $replace_sql, ", " );
					$wpdb->get_results( $replace_sql, ARRAY_A );
				}

				if ( count( $search_result ) ) {
					$result_in_tables ++;

					$myecho .= '<p><strong>' . __(
							'Table:', FB_SAR_TEXTDOMAIN
						) . ' </strong><code>' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . '</code> ... ';
					$myecho .= __( 'Total rows for', FB_SAR_TEXTDOMAIN ) . ' <code>"' . stripslashes(
							$search_text
						) . '"</code>: <strong>' . $wpdb->num_rows . '</strong></p>';
					$myecho .= '<p><a href="javascript:toggle(\'' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . '_sql' . '\')">SQL</a></p>';
					$myecho .= '<script language="JavaScript">
						table_id.push("' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . '_sql");
					</script>';
					// Display sql statement
					$sql = $search_sql;
					if ( $replace_text ) {
						$sql = $replace_sql;
					}
					$myecho .= '<div id="' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . '_sql" style="display:none;"><code>' . $sql . '</code></div>';
					$myecho .= '<p><a href="javascript:toggle(\'' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . '_wrapper' . '\')">Result</a></p>';
					$myecho .= '<script language="JavaScript">
						table_id.push("' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . '_wrapper");
					</script>';
					$myecho .= '<div id="' . $tables[ $i ][ 'Tables_in_' . $wpdb->dbname ] . '_wrapper" style="display:none;">';

					$myecho .= searchandreplace_table_arrange( $search_result );
					$myecho .= '</div>';
				}// @endof showing found search

			}

		}
	}

	if ( ! $result_in_tables ) {
		$myecho = '<p><code>' . esc_attr( $search_sql ) . '</code></p>';
		$myecho .= '<p style="color:red;">' . __( 'Sorry,' ) . ' <code>' .
			stripslashes_deep( stripslashes_deep( htmlentities2( $search_text ) ) ) . '</code> ' .
			__( 'is not found in this database', FB_SAR_TEXTDOMAIN ) .
			'(<code>' . $wpdb->dbname . '</code>)!</p>';
	}

	return $myecho;
}

/**
 * Taking the mySQL the result array and return html Table in a string.
 * Showing the search content with different css class.
 *
 * @param $array
 *
 * @return string
 */
function searchandreplace_table_arrange( $array ) {

	$table_data = ''; // @abstract	returning table
	$max        = 0; // @abstract	max length of a row
	$max_i      = 0; // @abstract	number of the row which is maximum max length of a row

	$search_text = $_POST[ "search_text" ];

	for ( $i = 0; $i < count( $array ); $i ++ ) {
		//@abstract table row
		$table_data .= '<tr class=' . ( ( $i & 1 ) ? '"alternate"' : '""' ) . ' >';
		$j = 0;

		foreach ( $array[ $i ] as $key => $data ) {

			$hint = '';
			if ( is_serialized( $data ) ) {
				$hint = '<div class="error below-h2 form-invalid">' . __(
						'Serialized, no changes possible.', FB_SAR_TEXTDOMAIN
					) . '</div>';
			}

			$data = preg_replace(
				"|($search_text)|U", "<code style=\"background:#ffc516;padding:0 4px;\"><b>$1</b></code>",
				htmlspecialchars( $data )
			);
			$table_data .= '<td>' . $hint . $data . ' &nbsp;</td>';
			$j ++;
		}

		if ( $max < $j ) {
			$max   = $j;
			$max_i = $i;
		}

		$table_data .= '</tr>' . "\n";
	}

	unset( $data );
	// @endof html table

	//@abstract populating the table head

	// @varname $data_a
	//@abstract  taking the highest sized array and printing the key name.
	$data_a = $array[ $max_i ];

	$table_head = '<tr>';
	foreach ( $data_a as $key => $value ) {
		$table_head .= '<th>' . $key . '</th>';
	}

	$table_head .= '</tr>' . "\n";

	// @abstract printing the table data
	return '<div class="table_bor">
		<table class="widefat">'
	. '<thead>' . $table_head . '</thead>'
	. '<tbody>' . $table_data . '</tbody>'
	. '</table>
		</div>';
}

/**
 * add js to the head that fires the 'new node' function
 */
function searchandreplace_add_js_head() {

	?>
	<script type="text/javascript">
		/* <![CDATA[ */
		function selectcb( thisobj, var1 ) {
			var i = 0;
			var o = document.forms[ thisobj ].elements;
			if ( o ) {
				for ( i = 0; i < o.length; i++ ) {
					if ( o[ i ].type == 'checkbox' ) {
						o[ i ].checked = var1;
					}
				}
			}
		}
		/* ]]> */
	</script>
	<?php
}

function searchandreplace_action() {

	if ( isset( $_POST[ 'submitted' ] ) ) {
		check_admin_referer( 'searchandreplace_nonce' );
		$myecho = '';
		if ( empty( $_POST[ 'search_text' ] ) ) {
			$myecho .= '<div class="error"><p><strong>&raquo; ' . __(
					'You must specify some text to replace!', FB_SAR_TEXTDOMAIN
				) . '</strong></p></div><br class="clear">';
		} else {
			$myecho .= '<div class="updated fade">';
			$myecho .= '<p><strong>&raquo; ' . __( 'Performing search', FB_SAR_TEXTDOMAIN );
			if ( ! isset( $_POST[ 'sall' ] ) ) {
				$_POST[ 'sall' ] = NULL;
			}
			if ( $_POST[ 'sall' ] == 'srall' ) {
				$myecho .= ' ' . __( 'and replacement', FB_SAR_TEXTDOMAIN );
			}
			$myecho .= ' ...</strong></p>';
			$myecho .= '<p>&raquo; ' . __( 'Searching for', FB_SAR_TEXTDOMAIN ) . ' <code>' . stripslashes(
					htmlentities2( $_POST[ 'search_text' ] )
				) . '</code>';
			if ( isset( $_POST[ 'replace_text' ] ) && $_POST[ 'sall' ] == 'srall' ) {
				$myecho .= __( 'and replacing with', FB_SAR_TEXTDOMAIN ) . ' <code>' . stripslashes(
						htmlentities2( $_POST[ 'replace_text' ] )
					) . '</code></p>';
			}
			$myecho .= '</div><br class="clear" />';

			if ( ! isset( $_POST[ 'replace_text' ] ) ) {
				$_POST[ 'replace_text' ] = NULL;
			}

			$error = searchandreplace_doit(
				$_POST[ 'search_text' ],
				$_POST[ 'replace_text' ],
				$_POST[ 'sall' ],
				isset( $_POST[ 'content' ] ),
				isset( $_POST[ 'guid' ] ),
				isset( $_POST[ 'id' ] ),
				isset( $_POST[ 'title' ] ),
				isset( $_POST[ 'excerpt' ] ),
				isset( $_POST[ 'meta_value' ] ),
				isset( $_POST[ 'comment_content' ] ),
				isset( $_POST[ 'comment_author' ] ),
				isset( $_POST[ 'comment_author_email' ] ),
				isset( $_POST[ 'comment_author_url' ] ),
				isset( $_POST[ 'comment_count' ] ),
				isset( $_POST[ 'cat_description' ] ),
				isset( $_POST[ 'tag' ] ),
				isset( $_POST[ 'user_id' ] ),
				isset( $_POST[ 'user_login' ] ),
				isset( $_POST[ 'singups' ] )
			);

			if ( $error != '' ) {
				$myecho .= $error;
			} else {
				$myecho .= '<p>' . __( 'Completed successfully!', FB_SAR_TEXTDOMAIN ) . '</p></div>';
			}
		}

		echo $myecho;
	}
}

function searchandreplace_page() {

	global $wpdb;

	if ( ! isset( $wpdb ) ) {
		$wpdb = NULL;
	}
	?>
	<div class="wrap" id="top">
		<h2><?php _e( 'Search &amp; Replace', FB_SAR_TEXTDOMAIN ); ?></h2>

		<?php
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			$capability = 'manage_options';
		} else {
			$capability = 'edit_plugins';
		}

		if ( current_user_can( $capability ) ) {
			searchandreplace_action();
		} else {
			wp_die(
				'<div class="error"><p>' . __(
					'You do not have sufficient permissions to edit plugins for this installation of WordPress.',
					FB_SAR_TEXTDOMAIN
				) . '</p></div>'
			);
		}
		?>

		<div id="poststuff" class="dlm">
			<div class="postbox">
				<h3><?php _e( 'Global Search &amp; Replace', FB_SAR_TEXTDOMAIN ) ?></h3>

				<div class="inside">
					<p><?php _e(
							'This plugin modifies your database directly!<br /><strong>WARNING: </strong>You <strong>cannot</strong> undo any changes made by this plugin. <strong>It is therefore recommended you backup your database before running this plugin.</strong> <a href="http://www.gnu.org/licenses/gpl-2.0.txt">There is no warranty for this plugin!</a> <strong>Activate</strong> the plugin <strong>only</strong> if you want to use it!',
							FB_SAR_TEXTDOMAIN
						); ?></p>

					<p><?php _e(
							'Text search is case sensitive and has no pattern matching capabilities. This replace function matches raw text so it can be used to replace HTML tags too.',
							FB_SAR_TEXTDOMAIN
						); ?></p>

					<p><?php _e(
							'But it will not replaced strings inside serialized data fields, like option values in the table options.',
							FB_SAR_TEXTDOMAIN
						); ?></p>

					<p><?php _e(
							'<strong>Step One:</strong> Use the folllowing search (only) first, for a better understanding of what will happen when you do the replace. The SQL query and tables will be returned with the results. The search uses all fields in all tables! After verifying your results you can use the replace function.',
							FB_SAR_TEXTDOMAIN
						); ?></p>

					<form name="search" action="" method="post">
						<?php wp_nonce_field( 'searchandreplace_nonce' ) ?>
						<table summary="config" class="widefat">
							<tr>
								<th>
									<label for="sall_label"><?php _e(
											'All - search only!', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td><input type='radio' name='sall' value='sall' id='sall_label' checked="checked" />
									<label for="sall_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>*</code> <?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>*</code></label>
								</td>
							</tr>
							<tr>
								<th><label for="search_text"><?php _e( 'Search for', FB_SAR_TEXTDOMAIN ); ?></label>
								</th>
								<td>
									<input class="code" type="text" id="search_text" name="search_text" value="" size="80" />
								</td>
							</tr>
							<tr class="alternate">
								<th>
									<label for="srall_label"><?php _e(
											'All - search and replace!', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td><input type='radio' name='sall' value='srall' id='srall_label' />
									<label for="srall_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>*</code> <?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>*</code></label>
								</td>
							</tr>
							<tr class="alternate">
								<th><label for="replace_text"><?php _e( 'Replace with', FB_SAR_TEXTDOMAIN ); ?></label>
								</th>
								<td>
									<input class="code" type="text" id="replace_text" name="replace_text" value="" size="80" />
								</td>
							</tr>
						</table>
						<p class="submit">
							<input class="button" type="submit" value="<?php _e(
								'Go', FB_SAR_TEXTDOMAIN
							); ?> &raquo;" />
							<input type="hidden" name="submitted" />
						</p>
					</form>
				</div>
			</div>
		</div>

		<div id="poststuff" class="dlm">
			<div class="postbox">
				<h3><?php _e( 'Search in', FB_SAR_TEXTDOMAIN ) ?></h3>

				<div class="inside">

					<form name="replace" action="" method="post">
						<?php wp_nonce_field( 'searchandreplace_nonce' ) ?>
						<table summary="config" class="widefat">
							<tr class="alternate">
								<th><label for="content_label"><?php _e( 'Content', FB_SAR_TEXTDOMAIN ); ?></label></th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='content' id='content_label' /></td>
								<td><label for="content_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>post_content</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_posts</code></label></td>
							</tr>
							<tr>
								<th>
									<label for="guid_label"><?php _e(
											'<abbr title="Global Unique Identifier">GUID</abbr>', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='guid' id='guid_label' /></td>
								<td><label for="guid_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>guid</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_posts</code></label></td>
							</tr>
							<tr class="alternate">
								<th><label for="title_label"><?php _e( 'Titles', FB_SAR_TEXTDOMAIN ); ?></label></th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='title' id='title_label' /></td>
								<td><label for="title_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>post_title</code>,
										<code>post_name</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_posts</code></label></td>
							</tr>
							<tr>
								<th><label for="excerpt_label"><?php _e( 'Excerpts', FB_SAR_TEXTDOMAIN ); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='excerpt' id='excerpt_label' /></td>
								<td><label for="excerpt_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>post_excerpt</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_posts</code></label></td>
							</tr>
							<tr class="alternate">
								<th><label for="meta_value_label"><?php _e( 'Metadata', FB_SAR_TEXTDOMAIN ); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='meta_value' id='meta_value_label' /></td>
								<td><label for="meta_value_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>meta_value</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_postmeta</code></label></td>
							</tr>
							<tr>
								<th>
									<label for="comment_content_label"><?php _e(
											'Comments content', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='comment_content' id='comment_content_label' /></td>
								<td><label for="comment_content_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>comment_content</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_comments</code></label></td>
							</tr>
							<tr class="alternate">
								<th>
									<label for="comment_author_label"><?php _e(
											'Comments author', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='comment_author' id='comment_author_label' /></td>
								<td><label for="comment_author_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>comment_author</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_comments</code></label></td>
							</tr>
							<tr>
								<th>
									<label for="comment_author_email_label"><?php _e(
											'Comments author e-mail', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='comment_author_email' id='comment_author_email_label' />
								</td>
								<td><label for="comment_author_email_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>comment_author_email</code><br /><?php _e(
											'Table:', FB_SAR_TEXTDOMAIN
										); ?>
										<code>_comments</code></label></td>
							</tr>
							<tr class="alternate">
								<th>
									<label for="comment_author_url_label"><?php _e(
											'Comments author URL', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='comment_author_url' id='comment_author_url_label' />
								</td>
								<td><label for="comment_author_url_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>comment_author_url</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_comments</code></label></td>
							</tr>
							<tr>
								<th>
									<label for="comment_count_label"><?php _e(
											'Comments counter', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='comment_count' id='comment_count_label' /></td>
								<td><label for="comment_count_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>comment_count</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_posts</code></label></td>
							</tr>
							<tr class="alternate">
								<th>
									<label for="cat_description_label"><?php _e(
											'Category description', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='cat_description' id='cat_description_label' /></td>
								<td><label for="cat_description_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>description</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_term_taxonomy</code></label></td>
							</tr>
							<tr>
								<th>
									<label for="tag_label"><?php _e(
											'Tags &amp; Categories', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='tag' id='tag_label' /></td>
								<td><label for="tag_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>name</code> <?php _e( 'and', FB_SAR_TEXTDOMAIN ); ?>
										<code>slug</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_terms</code></label></td>
							</tr>
							<tr class="alternate">
								<th><label for="user_id_label"><?php _e( 'User ID', FB_SAR_TEXTDOMAIN ); ?></label></th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='user_id' id='user_id_label' /></td>
								<td><label for="user_id_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>ID</code>, <code>user_id</code>, <code>post_author</code>,
										<code>user_id</code> <?php _e( 'and', FB_SAR_TEXTDOMAIN ); ?>
										<code>link_owner</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_users</code>, <code>_usermeta</code>, <code>_posts</code>,
										<code>_comments</code> <?php _e( 'and', FB_SAR_TEXTDOMAIN ); ?>
										<code>_links</code></label></td>
							</tr>
							<tr>
								<th>
									<label for="user_login_label"><?php _e(
											'User login', FB_SAR_TEXTDOMAIN
										); ?></label>
								</th>
								<td colspan="2" style="text-align: center;">
									<input type='checkbox' name='user_login' id='user_login_label' /></td>
								<td><label for="user_login_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
										<code>user_login</code> <?php _e( 'and', FB_SAR_TEXTDOMAIN ); ?>
										<code>user_nicename</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
										<code>_users</code></label></td>
							</tr>
							<?php if ( $wpdb
								&& $wpdb->query(
									"SHOW TABLES LIKE '" . $wpdb->prefix . 'terms' . "'"
								) == 1
							) { ?>
								<tr class="alternate">
									<th><label for="id_label"><?php _e( 'ID', FB_SAR_TEXTDOMAIN ); ?></label></th>
									<td colspan="2" style="text-align: center;">
										<input type='checkbox' name='id' id='id_label' /></td>
									<td><label for="id_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
											<code>ID</code>, <code>post_parent</code>, <code>post_id</code>,
											<code>object_id</code> <?php _e( 'and', FB_SAR_TEXTDOMAIN ); ?>
											<code>comments</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
											<code>_posts</code>, <code>_postmeta</code>,
											<code>_term_relationships</code> <?php _e( 'and', FB_SAR_TEXTDOMAIN ); ?>
											<code>_comment_post_ID</code></label></td>
								</tr>
							<?php } ?>
							<?php if ( $wpdb
								&& $wpdb->query(
									"SHOW TABLES LIKE '" . $wpdb->prefix . 'signups' . "'"
								) == 1
							) { ?>
								<tr class="alternate">
									<th><label for="signups_label"><?php _e( 'Signups', FB_SAR_TEXTDOMAIN ); ?></label>
									</th>
									<td colspan="2" style="text-align: center;">
										<input type='checkbox' name='signups' id='signups_label' /></td>
									<td><label for="signups_label"><?php _e( 'Field:', FB_SAR_TEXTDOMAIN ); ?>
											<code>user_login</code><br /><?php _e( 'Table:', FB_SAR_TEXTDOMAIN ); ?>
											<code>_signups</code></label></td>
								</tr>
							<?php } ?>
							<tr>
								<th>&nbsp;</th>
								<td colspan="2" style="text-align: center;">&nbsp;&nbsp;
									<a href="javascript:selectcb('replace', true);" title="<?php _e(
										'Check all', FB_SAR_TEXTDOMAIN
									); ?>"><?php _e( 'all', FB_SAR_TEXTDOMAIN ); ?></a> |
									<a href="javascript:selectcb('replace', false);" title="<?php _e(
										'Uncheck all', FB_SAR_TEXTDOMAIN
									); ?>"><?php _e( 'none', FB_SAR_TEXTDOMAIN ); ?></a>
								</td>
								<td>&nbsp;</td>
							</tr>
						</table>

						<table summary="submit" class="form-table">
							<tr>
								<th><label for="search_text"><?php _e( 'Search for', FB_SAR_TEXTDOMAIN ); ?></label>
								</th>
								<td>
									<input class="code" type="text" id="search_text" name="search_text" value="" size="80" />
								</td>
							</tr>
							<tr>
								<th><label for="replace_text"><?php _e( 'Replace with', FB_SAR_TEXTDOMAIN ); ?></label>
								</th>
								<td>
									<input class="code" type="text" id="replace_text" name="replace_text" value="" size="80" />
								</td>
							</tr>
						</table>
						<p class="submit">
							<input class="button" type="submit" value="<?php _e(
								'Go', FB_SAR_TEXTDOMAIN
							); ?> &raquo;" />
							<input type="hidden" name="submitted" />
						</p>
					</form>

				</div>
			</div>
		</div>

		<div id="poststuff" class="dlm">
			<div class="postbox">
				<h3><?php _e( 'Information about this plugin', FB_SAR_TEXTDOMAIN ); ?></h3>

				<div class="inside">
					<p><?php _e(
							"&quot;Search and Replace&quot; original plugin (en) created by <a href='http://thedeadone.net/'>Mark Cunningham</a> and provided (comments) by <a href='http://www.gonahkar.com'>Gonahkar</a>.<br />&quot;Search &amp; Replace&quot;, enhanced by <a href='http://bueltge.de'>Frank Bueltge</a> and current version is also maintained by Ron Guerin.",
							FB_SAR_TEXTDOMAIN
						); ?></p>

					<p><?php _e(
							"For more information: Visit the <a href='http://wordpress.org/plugins/search-and-replace/'>plugin homepage</a> for further information or to grab the latest version of this plugin.",
							FB_SAR_TEXTDOMAIN
						); ?></p>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
