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
	 * The search string
	 */
	protected $search;

	/**
	 * The replacement string
	 */
	protected $replace;

	/**
	 * The Database Interface Object
	 *
	 * @var Manager
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
	protected $dry_run = true;

	/**
	 * @var Service\MaxExecutionTime
	 */
	private $max_execution;

	/**
	 * Store error messages.
	 *
	 * @var
	 */
	private $errors;

	/**
	 * Store csv data
	 * @var array
	 */
	private $csv_data = array();

		/**
	 * Replace constructor.
	 *
	 * @param Manager                  $dbm
	 * @param Service\MaxExecutionTime $max_execution
	 */
	public function __construct( Manager $dbm, Service\MaxExecutionTime $max_execution ) {

		$this->dbm           = $dbm;
		$this->max_execution = $max_execution;
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
	 * @param null   $csv
	 *
	 * @return array|\WP_Error Collection of information gathered during the run.
	 */

	public function run_search_replace( $search, $replace, $tables, $csv = null ) {

		if ( $search === $replace && '' !== $search ) {
			return new \WP_Error( 'error', __( "Search and replace pattern can't be the same!" ) );
		}

		$this->max_execution->set();

		$report = array(
			'errors'        => null,
			'changes'       => array(),
			'tables'        => '0',
			'changes_count' => '0',
		);

		foreach ( (array) $tables as $table ) {
			//count tables
			$report ['tables'] ++;
			$table_report = $this->replace_values( $search, $replace, $table, $csv );
			//log changes if any

			if ( 0 !== $table_report['change'] ) {
				$report['changes'][ $table ] = $table_report;

				$report ['changes_count'] += $table_report['change'];
			}

		}

		$this->max_execution->restore();

		return $report;
	}

	/**
	 * @param string $search
	 * @param string $replace
	 * @param string $table
	 * @param null   $csv
	 *
	 * @return array
	 */
	public function replace_values( $search = '', $replace = '', $table, $csv = null ) {

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

		// Grab table structure in order to determine which columns are used to store serialized values in it.
		$table_structure = $this->dbm->get_table_structure( $table );
		if ( ! $table_structure ) {
			$this->errors->add(
				1,
				esc_attr__( 'Error getting table details', 'search-and-replace' ) . ': $table'
			);

			return $table_report;
		}
		$maybe_serialized = array();
		foreach ( $table_structure as $struct ) {
			// Longtext is used for meta_values as best practice in all of the automatic products.
			if ( 0 === stripos( $struct->Type, 'longtext' ) ) {
				$maybe_serialized[] = strtolower( $struct->Field );
			}
		}

		// check we have a search string, bail if not
		if ( empty( $search ) && empty( $csv ) ) {
			$table_report['errors'][] = 'Search string is empty';

			return $table_report;
		}
		//split columns array in primary key string and columns array
		$columns     = $this->dbm->get_columns( $table );
		list( $primary_key, $columns ) = $columns;

		if ( null === $primary_key ) {
			array_push(
				$table_report['errors'],
				"The table \"{$table}\" has no primary key. Changes will have to be made manually.",
				'results'
			);

			return $table_report;
		}

		$table_report['start'] = microtime();

		// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
		$row_count = $this->dbm->get_rows( $table );

		$page_size = $this->page_size;
		$pages     = ceil( $row_count / $page_size );

		// Prepare CSV data
		if ( $csv !== null ) {
			$csv_lines = explode( "\n", $csv );
			$csv_head  = str_getcsv( 'search,replace' );
			foreach ( $csv_lines as $line ) {
				$this->csv_data[] = array_combine( $csv_head, str_getcsv( $line ) );
			}
		}

		for ( $page = 0; $page < $pages; $page ++ ) {

			$start = $page * $page_size;

			// Grab the content of the table
			$data = $this->dbm->get_table_content( $table, $start, $page_size );

			if ( ! $data ) {
				$table_report['errors'][] = 'no data in table ' . $table;
			}

			foreach ( $data as $row ) {

				$table_report['rows'] ++;

				$update_sql = array();
				$where_sql  = array();
				$update     = false;

				foreach ( $columns as $column ) {
					//Skip the GUID column per Wordpress Codex
					if ( $column === 'guid' ) {
						continue;
					}
					$data_to_fix = $row[ $column ];

					if ( $column === $primary_key ) {
						$where_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $data_to_fix ) . '"';
						continue;
					}

					// Run a search replace on the data that'll respect the serialisation.
					if ( in_array( strtolower( $column ), $maybe_serialized, true ) ) {
						// Run a search replace on the data that'll respect the serialisation.
						$edited_data = $this->recursive_unserialize_replace( $search, $replace, $data_to_fix );
					} else {
						$edited_data = str_replace( $search, $replace, $data_to_fix );
					}

					// Run a search replace by CSV parameters if CSV input present
					if ( $csv !== null ) {
						foreach ( $this->csv_data as $entry ) {
							$edited_data = $this->recursive_unserialize_replace( $entry['search'],
								$entry['replace'], $edited_data );
						}
					}

					// Something was changed
					if ( $edited_data !== $data_to_fix ) {

						$table_report['change'] ++;

						// log changes
						//TODO : does it work with non UTF-8 encodings?
						$table_report['changes'][] = array(
							'row'    => $table_report['rows'],
							'column' => $column,
							'from'   => $data_to_fix,
							'to'     => $edited_data,
						);

						$update_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $edited_data ) . '"';
						$update       = true;

					}

				}

				// Determine what to do with updates.
				if ( true === $this->dry_run ) {
					// Don't do anything if a dry run
				} elseif ( $update && ! empty( $where_sql ) ) {
					// If there are changes to make, run the query.

					$result = $this->dbm->update( $table, $update_sql, $where_sql );

					if ( ! $result ) {
						$table_report['errors'][] = sprintf(
							__( 'Error updating row: %d.', 'search-and-replace' ),
							$row
						);
					} else {
						$table_report['updates'] ++;
					}

				}
			}
		}

		$table_report['end'] = microtime( true );
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
	public function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false ) {

		// some unserialized data cannot be re-serialised eg. SimpleXMLElements
		try {

			if ( is_string( $data ) && is_serialized( $data ) && ( $unserialized = @unserialize( $data, false ) ) !== false ) {
				// Changed to maybe_unserialize because wp serialization != php serialization.
				$data = $this->recursive_unserialize_replace( $from, $to, $unserialized, true );
			} elseif ( is_array( $data ) ) {
				$_tmp = array();
				foreach ( (array) $data as $key => $value ) {
					$_tmp[ $key ] = $this->recursive_unserialize_replace( $from, $to, $value, false );
				}

				$data = $_tmp;
				unset( $_tmp );
			} elseif ( is_object( $data ) ) {
				$_tmp  = $data;
				$props = get_object_vars( $data );
				foreach ( $props as $key => $value ) {
					$_tmp->$key = $this->recursive_unserialize_replace( $from, $to, $value );
				}

				$data = $_tmp;
				unset( $_tmp );
			} else {
				if ( is_string( $data ) ) {

					$marker = false;
					if ( is_serialized_string( $data ) ) {
						$data   = @unserialize( $data );
						$marker = true;
					}

					// Do not allow to return valid serialized data,
					// If after replacement data is_serialized then add one | to the replacement.
					$tmp_data = $data;
					$data     = str_replace( $from, $to, $data );
					if ( is_serialized( $data, false ) ) {
						$data = str_replace( $from, '|' . $to, $tmp_data );
					}

					if ( $marker ){
						$data = maybe_serialize($data);
					}

				} else {
					$data = str_replace( $from, $to, $data );
					$data = serialize( $data );
				}
			}

			if ( $serialised ) {
				return serialize( $data );
			}

		}
		catch ( \Exception $error ) {

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

	protected function is_json( $string, $strict = false ) {

		$json = @json_decode( $string, true );
		if ( $strict === true && ! is_array( $json ) ) {
			return false;
		}

		return ! ( $json === null || $json === false );
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

	/**
	 * @param $getMessage
	 * @param $string
	 */
	private function add_error( $getMessage, $string ) {
	}

}
