<?php
/**
 *  User: S Hinse #TODO remove unused space
 * Date: 26.10.2015
 * Time: 12:33
 */

namespace Inpsyde\searchReplace\inc;

class DatabaseManager {

	/**
	 * @var \wpdb
	 * Wordpress Database Class
	 */
	private $wpdb;

	public function __construct() {

		global $wpdb;
		$this->wpdb = $wpdb;

	}

	/**
	 * Returns an array of tables in the database.
	 *
	 * @access public
	 * @return array
	 */
	public function get_tables() {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( is_main_site() ) {
				$tables = $this->wpdb->get_col( 'SHOW TABLES' );
			} else {
				$blog_id = get_current_blog_id();
				$tables  = $this->wpdb->get_col( "SHOW TABLES LIKE '" . $this->wpdb->base_prefix . absint( $blog_id ) . "\_%'" );
			}

		} else {
			$tables = $this->wpdb->get_col( 'SHOW TABLES' );
		}

		return $tables;
	}

	/**
	 * Returns an array containing the size of each database table.
	 *
	 * @access public
	 * @return array
	 */
	public function get_sizes() {

		$sizes  = array();
		$tables = $this->wpdb->get_results( 'SHOW TABLE STATUS', ARRAY_A );

		if ( is_array( $tables ) && ! empty( $tables ) ) {

			foreach ( $tables as $table ) {
				$size                      = round( $table[ 'Data_length' ] / 1024, 2 );
				$sizes[ $table[ 'Name' ] ] = sprintf( __( '(%s KB)', 'insr' ), $size );
			}

		}

		return $sizes;
	}

	/**
	 * Returns the number of rows in a table.
	 *
	 * @access public
	 * @return int
	 */
	public function get_rows( $table ) {

		$table = esc_sql( $table );
		$rows  = $this->wpdb->get_var( "SELECT COUNT(*) FROM $table" );

		return $rows;
	}

	/**
	 * Gets the columns in a table.
	 *
	 * @access public
	 *
	 * @param  string $table The table to check.
	 *
	 * @return array
	 */
	public function get_columns( $table ) {

		$primary_key = NULL;
		$columns     = array();
		$fields      = $this->wpdb->get_results( 'DESCRIBE ' . $table );

		if ( is_array( $fields ) ) {
			foreach ( $fields as $column ) {
				$columns[] = $column->Field;
				if ( $column->Key == 'PRI' ) {
					$primary_key = $column->Field;
				}
			}
		}

		return array( $primary_key, $columns );
	}

	public function get_table_content( $table, $start, $end ) {

		$data = $this->wpdb->get_results( "SELECT * FROM $table LIMIT $start, $end", ARRAY_A );

		return $data;
	}

	public function update( $table, $update_sql, $where_sql ) {

		#TODO remove line break
		$sql    = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ',
		                                                                                             array_filter( $where_sql ) );
		$result = $this->wpdb->query( $sql );

		return $result;

	}

	public function flush() {

		$this->wpdb->flush();
	}

}


