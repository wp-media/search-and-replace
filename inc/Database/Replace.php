<?php
namespace Inpsyde\SearchReplace\Database;

use Inpsyde\SearchReplace\Service;

/**
 * Class Replace
 * runs search & replace on a database
 * adapted from: https://github.com/interconnectit/Search-Replace-DB/blob/master/srdb.class.php
 *
 * @package Inpsyde\SearchReplace\Database
 */
class Replace {

	/**
	 * the  search string
	 *
	 * @var
	 */
	protected $search;

	/**
	 *  the replacement string
	 *
	 * @var
	 */
	protected $replace;

	/**
	 * The Database Interface Object
	 *
	 * @type Manager
	 * @var
	 */
	protected $dbm;

	/**
	 * Count of rows to be replaced at a time
	 *
	 * @var int
	 */
	protected $page_size = 100;

	/**
	 * @var bool - set if dry run
	 */
	protected $dry_run = TRUE;

	/**
	 * Replace constructor.
	 *
	 * @param Manager $dbm
	 */
	public function __construct( Manager $dbm ) {

		$this->dbm = $dbm;
	}

	/**
	 * The main loop triggered in step 5. Up here to keep it out of the way of the
	 * HTML. This walks every table in the db that was selected in step 3 and then
	 * walks every row and column replacing all occurrences of a string with another.
	 * We split large tables into  blocks (size is set via $page_size)when dealing with them to save
	 * on memory consumption.
	 *
	 * @param string $search  What we want to replace
	 * @param string $replace What we want to replace it with.
	 * @param string $tables  The name of the table we want to look at.
	 *
	 * @return array    Collection of information gathered during the run.
	 */

	public function run_search_replace( $search, $replace, $tables ) {

		if ( $search === $replace ){
			return new \WP_Error( 'error', __( "Search and replace pattern can't be the same!" ) );
		}

		$execution_time = new Service\MaxExecutionTime();
		$execution_time->set();

		$report = array(
			'errors'        => NULL,
			'changes'       => array(),
			'tables'        => '0',
			'changes_count' => '0',
		);

		foreach ( (array) $tables as $table ) {
			//count tables
			$report [ 'tables' ] ++;
			$table_report = $this->replace_values( $search, $replace, $table );
			//log changes if any

			if ( 0 !== $table_report[ 'change' ] ) {
				$report[ 'changes' ][ $table ] = $table_report;

				$report [ 'changes_count' ] += $table_report[ 'change' ];
			}

		}

		$execution_time->restore();

		return $report;
	}

	public function replace_values( $search = '', $replace = '', $table ) {

		$table_report = array(
			'table_name' => $table,
			'rows'       => 0,
			'change'     => 0,
			'changes'    => array(),
			'updates'    => 0,
			'start'      => microtime(),
			'end'        => microtime(),
			'errors'     => array(),
		);

		// check we have a search string, bail if not
		if ( empty( $search ) ) {
			$table_report[ 'errors' ][] = 'Search string is empty';

			return $table_report;
		}
		//split columns array in primary key string and columns array
		$columns     = $this->dbm->get_columns( $table );
		$primary_key = $columns[ 0 ];
		$columns     = $columns[ 1 ];

		if ( NULL === $primary_key ) {
			array_push(
				$table_report[ 'errors' ],
				"The table \"{$table}\" has no primary key. Changes will have to be made manually.",
				'results'
			);

			return $table_report;
		}

		$table_report[ 'start' ] = microtime();

		// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
		$row_count = $this->dbm->get_rows( $table );

		$page_size = $this->page_size;
		$pages     = ceil( $row_count / $page_size );

		for ( $page = 0; $page < $pages; $page ++ ) {

			$start = $page * $page_size;

			// Grab the content of the table
			$data = $this->dbm->get_table_content( $table, $start, $page_size );

			if ( ! $data ) {
				$table_report[ 'errors' ][] = 'no data in table ' . $table;
			}

			foreach ( $data as $row ) {

				$table_report[ 'rows' ] ++;

				$update_sql = array();
				$where_sql  = array();
				$update     = FALSE;

				foreach ( $columns as $column ) {

					$data_to_fix = $row[ $column ];

					if ( $column === $primary_key ) {
						$where_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $data_to_fix ) . '"';
						continue;
					}
					/*	// exclude cols
						if ( in_array( $column, $this->exclude_cols ) )
							continue;

						// include cols
						if ( ! empty( $this->include_cols ) && ! in_array( $column, $this->include_cols ) )
							continue;*/

					// Run a search replace on the data that'll respect the serialisation.
					$edited_data = $this->recursive_unserialize_replace( $search, $replace, $data_to_fix );

					// Something was changed
					if ( $edited_data !== $data_to_fix ) {

						$table_report[ 'change' ] ++;

						// log changes
						//TODO : does it work with non UTF-8 encodings?
						$table_report[ 'changes' ][] = array(
							'row'    => $table_report[ 'rows' ],
							'column' => $column,
							'from'   => $data_to_fix,
							'to'     => $edited_data,
						);

						$update_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $edited_data ) . '"';
						$update       = TRUE;

					}

				}

				// Determine what to do with updates.
				if ( TRUE === $this->dry_run ) {
					// Don't do anything if a dry run
				} elseif ( $update && ! empty( $where_sql ) ) {
					// If there are changes to make, run the query.

					$result = $this->dbm->update( $table, $update_sql, $where_sql );

					if ( ! $result ) {
						$table_report[ 'errors' ][] = sprintf(
							__( 'Error updating row: %d.', 'search-and-replace' ),
							$row
						);
					} else {
						$table_report[ 'updates' ] ++;
					}

				}
			}
		}

		$table_report[ 'end' ] = microtime( TRUE );
		$this->dbm->flush();

		return $table_report;

	}

	/**
	 * Take a serialised array and unserialize it replacing elements as needed and
	 * unserializing any subordinate arrays and performing the replace on those too.
	 *
	 * @param string              $from       String we're looking to replace.
	 * @param string              $to         What we want it to be replaced with
	 * @param array|string|object $data       Used to pass any subordinate arrays back to in.
	 * @param bool                $serialised Does the array passed via $data need serialising.
	 *
	 * @return array The original array with all elements replaced as needed.
	 */
	public function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = FALSE ) {

		// some unserialized data cannot be re-serialised eg. SimpleXMLElements
		try {

			if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== FALSE ) {
				$data = $this->recursive_unserialize_replace( $from, $to, $unserialized, TRUE );
			} elseif ( is_array( $data ) ) {
				$_tmp = array();
				foreach ( $data as $key => $value ) {
					$_tmp[ $key ] = $this->recursive_unserialize_replace( $from, $to, $value, FALSE );
				}

				$data = $_tmp;
				unset( $_tmp );
			} // Submitted by Tina Matter
			elseif ( is_object( $data ) ) {
				// $data_class = get_class( $data );
				$_tmp  = $data; // new $data_class( );
				$props = get_object_vars( $data );
				foreach ( $props as $key => $value ) {
					$_tmp->$key = $this->recursive_unserialize_replace( $from, $to, $value, FALSE );
				}

				$data = $_tmp;
				unset( $_tmp );
			} else {
				if ( is_string( $data ) ) {
					$data = str_replace( $from, $to, $data );

				}
			}

			if ( $serialised ) {
				return serialize( $data );
			}

		}
		catch ( Exception $error ) {

			$this->add_error( $error->getMessage(), 'results' );

		}

		return $data;
	}

	/**
	 * Checks if the submitted string is a JSON object
	 *
	 * @param            $string
	 * @param bool|FALSE $strict
	 *
	 * @return bool
	 */

	protected function is_json( $string, $strict = FALSE ) {

		$json = @json_decode( $string, TRUE );
		if ( $strict === TRUE && ! is_array( $json ) ) {
			return FALSE;
		}

		return ! ( $json === NULL || $json === FALSE );
	}

	/**
	 * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
	 *
	 * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
	 * @access public
	 *
	 * @param  array|string $input The string to escape.
	 *
	 * @return string
	 */

	public function mysql_escape_mimic( $input ) {

		if ( is_array( $input ) ) {
			return array_map( __METHOD__, $input );
		}
		if ( ! empty( $input ) && is_string( $input ) ) {
			return str_replace(
				array( '\\', "\0", "\n", "\r", "'", '"', "\x1a" ),
				array( '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ),
				$input
			);
		}

		return $input;
	}

	/**
	 * Sets the dry run option.
	 *
	 * @param bool $state : TRUE for dry run, FALSE for writing changes to DB
	 *
	 * @return bool
	 */
	public function set_dry_run( $state ) {

		if ( is_bool( $state ) ) {

			$this->dry_run = $state;
		}

		return $state;
	}

	/**
	 * Returns true, if dry run, false if not
	 *
	 * @return bool
	 */
	public function get_dry_run() {

		return $this->dry_run;
	}

}