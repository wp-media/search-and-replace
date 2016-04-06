<?php
namespace Inpsyde\SearchReplace\Database;

/**
 * Class Manager
 *
 * @package Inpsyde\SearchReplace\Database
 */
class Manager {

	/**
	 * @var \wpdb
	 * Wordpress Database Class
	 * some functions adapted from :
	 * https://github.com/ExpandedFronts/Better-Search-Replace/blob/master/includes/class-bsr-db.php
	 */
	private $wpdb;

	/**
	 * DatabaseManager constructor.
	 *
	 * @param \wpdb $wpdb
	 */
	public function __construct( \wpdb $wpdb ) {

		$this->wpdb = $wpdb;
	}

	/**
	 * Returns an array of tables in the database.
	 * if multisite && mainsite: all tables of the site
	 * if multisite && subsite: all tables of current blog
	 * if single site : all tabkes of the site
	 *
	 * @access public
	 * @return array
	 */
	public function get_tables() {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( is_main_site() ) {
				$tables = $this->wpdb->get_col( "SHOW TABLES LIKE'" . $this->wpdb->base_prefix . "%'" );
			} else {
				$blog_id = get_current_blog_id();
				$tables  = $this->wpdb->get_col( "SHOW TABLES LIKE '" . $this->wpdb->base_prefix . absint( $blog_id ) . "\_%'" );
			}

		} else {
			$tables = $this->wpdb->get_col( "SHOW TABLES LIKE'" . $this->wpdb->base_prefix . "%'" );
		}

		return $tables;
	}

	/**
	 * Returns an array containing the size of each database table.
	 *
	 * @access public
	 * @return array  Table => Table Size in KB
	 */
	public function get_sizes() {

		$sizes  = array();
		$tables = $this->wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );

		if ( is_array( $tables ) && ! empty( $tables ) ) {

			foreach ( $tables as $table ) {
				$size                      = round( $table[ 'Data_length' ] / 1024, 2 );
				$sizes[ $table[ 'Name' ] ] = sprintf( __( '(%s KB)', 'search-and-replace' ), $size );
			}

		}

		return $sizes;
	}

	/**
	 * Returns the number of rows in a table.
	 *
	 * @access public
	 *
	 * @param $table
	 *
	 * @return int
	 */
	public function get_rows( $table ) {

		$table = esc_sql( $table );

		return $this->wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	/**
	 * Gets the columns in a table.
	 *
	 * @access public
	 *
	 * @param  string $table The table to check.
	 *
	 * @return array 1st Element: Primary Key, 2nd Element All Columns
	 */
	public function get_columns( $table ) {

		$primary_key = NULL;
		$columns     = array();
		$fields      = $this->wpdb->get_results( 'DESCRIBE ' . $table );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				$columns[] = $column->Field;
				if ( 'PRI' === $column->Key ) {
					$primary_key = $column->Field;
				}
			}
		}

		return array( $primary_key, $columns );
	}

	/**
	 * @param $table String The Table Name
	 * @param $start Int The start row
	 * @param $end   Int Number of Rows to be fetched
	 *
	 * @return array|null|object
	 */
	public function get_table_content( $table, $start, $end ) {

		$data = $this->wpdb->get_results( "SELECT * FROM $table LIMIT $start, $end", ARRAY_A );

		return $data;
	}

	/**
	 * Update table.
	 *
	 * @param $table
	 * @param $update_sql
	 * @param $where_sql
	 *
	 * @return false|int
	 */
	public function update( $table, $update_sql, $where_sql ) {

		$sql = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) .
		       ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );

		return $this->wpdb->query( $sql );
	}

	/**
	 * Get table structure.
	 *
	 * @param $table
	 *
	 * @return array|null|object
	 */
	public function get_table_structure( $table ) {

		return $this->wpdb->get_results( "DESCRIBE $table" );
	}

	/**
	 * returns a SQL CREATE TABLE Statement for the table provided in $table
	 *
	 * @param $table String The Name of the table we want to create the statement for.
	 *
	 * @return string
	 */
	public function get_create_table_statement( $table ) {

		return $this->wpdb->get_results( "SHOW CREATE TABLE $table", ARRAY_N );
	}

	/**
	 * Flush table.
	 */
	public function flush() {

		$this->wpdb->flush();
	}

	/**
	 * Get base prefix.
	 *
	 * @return string
	 */
	public function get_base_prefix() {

		return $this->wpdb->base_prefix;
	}

}