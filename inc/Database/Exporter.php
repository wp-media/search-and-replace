<?php

namespace Inpsyde\SearchReplace\Database;

/**
 * Class Exporter
 *
 * @package Inpsyde\SearchReplace\Database
 */
class Exporter {

	/**
	 * @Stores all error messages in a WP_Error Object
	 */
	protected $errors;

	/**
	 * @string  The Path to the Backup Directory
	 */
	protected $backup_dir;

	/**
	 * @var Replace
	 */
	protected $replace;

	/**
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
	 * @String stores the filename of the backup file
	 */
	protected $backup_filename;

	/**
	 * Store file path.
	 *
	 * @var $fp
	 */
	protected $fp;

	/**
	 * Exporter constructor.
	 *
	 * @param Replace $replace
	 * @param Manager $dbm
	 */
	public function __construct( Replace $replace, Manager $dbm ) {

		$this->errors     = new \WP_Error();
		$this->backup_dir = get_temp_dir();
		$this->replace    = $replace;
		$this->dbm        = $dbm;
	}

	/**
	 * Write to the backup file
	 *
	 * @param string $search
	 * @param string $replace
	 * @param array  $tables         The array of table names that should be exported.
	 * @param bool   $domain_replace If set, exporter will change the domain name without leading http:// in table
	 *                               wp_blogs if we are on a multisite
	 * @param        $new_table_prefix
	 *
	 * @return array $report    $report [ 'filename'] : Name of Backup file,
	 *                          $report[ 'errors'] : WP_Error_object,
	 * $report ['changes'] : Array with replacements in tables
	 */
	public function db_backup( $search = '', $replace = '', $tables = array(), $domain_replace = FALSE, $new_table_prefix = '' ) {

		if ( count( $tables ) < 1 ) {
			$tables = $this->dbm->get_tables();
		}

		$report = array(
			'errors'        => NULL,
			'changes'       => array(),
			'tables'        => '0',
			'changes_count' => '0',
			'filename'      => '',
		);

		$table_prefix = $this->dbm->get_base_prefix();

		// wp_blogs needs special treatment in multisite domain replace, we need to check later if we are working on it.
		$wp_blogs_table = $table_prefix . 'blogs';

		$this->backup_filename = $new_table_prefix === '' ? DB_NAME . "_$table_prefix.sql"
			: DB_NAME . "_$new_table_prefix.sql";

		if ( is_writable( $this->backup_dir ) ) {
			$this->fp = $this->open( $this->backup_dir . $this->backup_filename );
			if ( ! $this->fp ) {
				$this->errors->add(
					8, esc_attr__( 'Could not open the backup file for writing!', 'search-and-replace' )
				);

				return $report;
			}
		} else {
			$this->errors->add( 9, esc_attr__( 'The backup directory is not writable!', 'search-and-replace' ) );

			return $report;
		}

		//Begin new backup of MySql
		//get charset. if not set assume utf8
		$charset = ( defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8' );
		$this->stow( '# ' . esc_attr__( 'WordPress MySQL database backup', 'search-and-replace' ) . "\n" );
		$this->stow( "#\n" );
		$this->stow( '# ' . sprintf( __( 'Generated: %s', 'search-and-replace' ), date( 'l j. F Y H:i T' ) ) . "\n" );
		$this->stow( '# ' . sprintf( __( 'Hostname: %s', 'search-and-replace' ), DB_HOST ) . "\n" );
		$this->stow( '# ' . sprintf( __( 'Database: %s', 'search-and-replace' ), $this->backquote( DB_NAME ) ) . "\n" );
		if ( '' !== $new_table_prefix ) {
			$this->stow(
				'# ' . sprintf(
					__( 'Changed table prefix: From %s to %s ', 'search-and-replace' ),
					$table_prefix,
					$new_table_prefix
				)
				. "\n"
			);
		}
		$this->stow( "# --------------------------------------------------------\n" );

		$this->stow( "/*!40101 SET NAMES $charset */;\n" );
		$this->stow( "# --------------------------------------------------------\n" );
		foreach ( $tables as $table ) {

			//count tables
			$report [ 'tables' ] ++;

			/**
			 * Check if we are replacing the domain in a multisite.
			 * If so, we replace in wp_blogs the stripped url without http(s), because the domains
			 * are stored without http://
			 */
			if ( $domain_replace && is_multisite() && $table === $wp_blogs_table ) {

				$stripped_url_search  = substr( $search, strpos( $search, '/' ) + 2 );
				$stripped_url_replace = substr( $replace, strpos( $replace, '/' ) + 2 );
				$table_report         = $this->backup_table(
					$stripped_url_search,
					$stripped_url_replace,
					$table,
					$new_table_prefix
				);

			} else {
				$table_report = $this->backup_table( $search, $replace, $table, $new_table_prefix );
			}
			//log changes if any

			if ( 0 !== $table_report[ 'change' ] ) {
				$report[ 'changes' ][ $table ] = $table_report;

				$report [ 'changes_count' ] += $table_report[ 'change' ];
			}
		}

		$this->close( $this->fp );
		//return errors if any
		if ( count( $this->errors->get_error_codes() ) ) {
			$report[ 'errors' ] = $this->errors;
		}
		$report [ 'filename' ] = $this->backup_filename;

		return $report;

	}

	/**
	 * Taken partially from phpMyAdmin and partially from
	 * Alain Wolf, Zurich - Switzerland
	 * Website: http://restkultur.ch/personal/wolf/scripts/db_backup/
	 * Modified by Scott Merrill (http://www.skippy.net/)
	 * to use the WordPress $wpdb object
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $table
	 * @param string $new_table_prefix
	 *
	 * @return array $table_report Reports the changes made to the db.
	 */

	public function backup_table( $search = '', $replace = '', $table, $new_table_prefix = '' ) {

		$table_report = array(
			'table_name' => $table,
			'rows'       => 0,
			'change'     => 0,
			'changes'    => [ ],
		);
		//do we need to replace the prefix?
		$table_prefix = $this->dbm->get_base_prefix();
		$new_table    = $table;
		if ( '' !== $new_table_prefix ) {
			$new_table = $this->get_new_table_name( $table, $new_table_prefix );

		}

		// Create the SQL statements
		$this->stow( '# --------------------------------------------------------' . "\n" );
		$this->stow( '# ' . sprintf( __( 'Table: %s', 'search-and-replace' ), $this->backquote( $new_table ) ) . "\n" );

		$table_structure = $this->dbm->get_table_structure( $table );
		if ( ! $table_structure ) {
			$this->errors->add( 1, __( 'Error getting table details', 'search-and-replace' ) . ": $table" );

			return $table_report;
		}

		$this->stow( "\n\n" );
		$this->stow( "#\n" );
		$this->stow(
			'# ' . sprintf(
				__( 'Delete any existing table %s', 'search-and-replace' ),
				$this->backquote( $new_table )
			) . "\n"
		);
		$this->stow( "#\n" );
		$this->stow( "\n" );
		$this->stow( 'DROP TABLE IF EXISTS ' . $this->backquote( $new_table ) . ';' . "\n" );

		// Table structure
		// Comment in SQL-file
		$this->stow( "\n\n" );
		$this->stow( "#\n" );
		$this->stow(
			'# ' . sprintf(
				__( 'Table structure of table %s', 'search-and-replace' ),
				$this->backquote( $new_table )
			) . "\n"
		);
		$this->stow( "#\n" );
		$this->stow( "\n" );

		/** @var array $create_table */
		$create_table = $this->dbm->get_create_table_statement( $table );
		if ( FALSE === $create_table ) {
			$err_msg = sprintf( __( 'Error with SHOW CREATE TABLE for %s.', 'search-and-replace' ), $table );
			$this->errors->add( 2, $err_msg );
			$this->stow( "#\n# $err_msg\n#\n" );
		}
		//replace prefix if necessary
		if ( '' !== $new_table_prefix ) {

			$create_table[ 0 ][ 1 ] = str_replace( $table, $new_table, $create_table[ 0 ][ 1 ] );

		}
		$this->stow( $create_table[ 0 ][ 1 ] . ' ;' );

		if ( FALSE === $table_structure ) {
			$err_msg = sprintf( __( 'Error getting table structure of %s', 'search-and-replace' ), $table );
			$this->errors->add( 3, $err_msg );
			$this->stow( "#\n# $err_msg\n#\n" );
		}

		// Comment in SQL-file
		$this->stow( "\n\n" );
		$this->stow( "#\n" );
		$this->stow(
			'# ' . sprintf(
				__( 'Data contents of table %s', 'search-and-replace' ),
				$this->backquote( $new_table )
			) . "\n"
		);
		$this->stow( "#\n" );

		$defs = array();
		$ints = array();
		foreach ( $table_structure as $struct ) {
			if ( ( 0 === strpos( $struct->Type, 'tinyint' ) )
				|| ( 0 === strpos( strtolower( $struct->Type ), 'smallint' ) )
				|| ( 0 === strpos( strtolower( $struct->Type ), 'mediumint' ) )
				|| ( 0 === strpos( strtolower( $struct->Type ), 'int' ) )
				|| ( 0 === strpos( strtolower( $struct->Type ), 'bigint' ) )
			) {
				$defs[ strtolower( $struct->Field ) ] = ( NULL === $struct->Default ) ? 'NULL' : $struct->Default;
				$ints[ strtolower( $struct->Field ) ] = '1';
			}
		}

		//split columns array in primary key string and columns array
		$columns     = $this->dbm->get_columns( $table );
		$primary_key = $columns[ 0 ];

		$row_count = $this->dbm->get_rows( $table );

		$page_size = $this->page_size;
		$pages     = ceil( $row_count / $page_size );

		for ( $page = 0; $page < $pages; $page ++ ) {
			$start = $page * $page_size;

			$table_data = $this->dbm->get_table_content( $table, $start, $page_size );

			$entries = 'INSERT INTO ' . $this->backquote( $new_table ) . ' VALUES (';
			//    \x08\\x09, not required
			$hex_search  = array( "\x00", "\x0a", "\x0d", "\x1a" );
			$hex_replace = array( '\0', '\n', '\r', '\Z' );
			if ( $table_data ) {
				foreach ( $table_data as $row ) {
					$values = array();
					$table_report[ 'rows' ] ++;

					foreach ( $row as $column => $value ) {
						//if "change database prefix" is set we have to look for occurrences of the old prefix in the db entries and change them
						if ( $new_table !== $table ) {
							$value = $this->replace->recursive_unserialize_replace(
								$table_prefix, $new_table_prefix,
								$value
							);
						}
						//skip replace if no search pattern
						//check if we need to replace something
						//skip primary_key
						if ( $search !== '' && $column !== $primary_key ) {

							$edited_data = $this->replace->recursive_unserialize_replace(
								$search, $replace,
								$value
							);

							// Something was changed
							if ( $edited_data !== $value ) {

								$table_report[ 'change' ] ++;

								// log changes

								$table_report[ 'changes' ][] = array(
									'row'    => $table_report[ 'rows' ],
									'column' => $column,
									'from'   => $value,
									'to'     => $edited_data,
								);
								$value                       = $edited_data;

							}

						}
						if ( isset( $ints[ strtolower( $column ) ] ) ) {
							// make sure there are no blank spots in the insert syntax,
							// yet try to avoid quotation marks around integers
							$value    = ( NULL === $value || '' === $value ) ? $defs[ strtolower( $column ) ] : $value;
							$values[] = ( '' === $value ) ? "''" : $value;
						} else {
							$values[] = "'" . str_replace(
									$hex_search, $hex_replace,
									$this->sql_addslashes( $value )
								) . "'";
						}
					}
					$this->stow( " \n" . $entries . implode( ', ', $values ) . ');' );
				}

			}
		}

		// Create footer/closing comment in SQL-file
		$this->stow( "\n" );
		$this->stow( "#\n" );
		$this->stow(
			'# ' . sprintf(
				__( 'End of data contents of table %s', 'search-and-replace' ),
				$this->backquote( $new_table )
			) . "\n"
		);
		$this->stow( "# --------------------------------------------------------\n" );
		$this->stow( "\n" );

		return $table_report;

	}

	/**
	 * Better addslashes for SQL queries.
	 * Taken from phpMyAdmin.
	 *
	 * @param string $a_string
	 * @param bool   $is_like
	 *
	 * @return mixed
	 */
	protected function sql_addslashes( $a_string = '', $is_like = FALSE ) {

		if ( $is_like ) {
			$a_string = str_replace( '\\', '\\\\\\\\', $a_string );
		} else {
			$a_string = str_replace( '\\', '\\\\', $a_string );
		}

		return str_replace( '\'', '\\\'', $a_string );
	}

	/**
	 * Add backquotes to tables and db-names in
	 * SQL queries. Taken from phpMyAdmin.
	 *
	 * @param $a_name
	 *
	 * @return array|string
	 */
	protected function backquote( $a_name ) {

		if ( ! empty( $a_name ) && $a_name !== '*' ) {
			if ( is_array( $a_name ) ) {
				$result = array();
				reset( $a_name );
				while ( list( $key, $val ) = each( $a_name ) ) {
					$result[ $key ] = '`' . $val . '`';
				}

				return $result;
			} else {
				return '`' . $a_name . '`';
			}
		} else {
			return $a_name;
		}
	}

	protected function open( $filename = '', $mode = 'w' ) {

		if ( '' === $filename ) {
			return FALSE;
		}

		return @fopen( $filename, $mode );
	}

	protected function close( $fp ) {

		fclose( $fp );
	}

	/**
	 * writes a line to the backup file
	 *
	 * @param $query_line
	 */
	protected function stow( $query_line ) {

		if ( @fwrite( $this->fp, $query_line ) === FALSE ) {
			$this->errors->add(
				4,
				esc_attr__( 'There was an error writing a line to the backup script:', 'search-and-replace' )
				. ' ' . $query_line . ' ' . $php_errormsg
			);
		}
	}

	/**
	 * @param string $filename The name of the file to be downloaded
	 * @param bool   $compress If TRUE, gz compression is used
	 *
	 * @return bool FALSE if error , has to DIE when file is delivered
	 */
	public function deliver_backup( $filename = '', $compress = FALSE ) {

		if ( '' === $filename ) {
			return FALSE;
		}

		$diskfile = $this->backup_dir . $filename;
		//compress file if set
		if ( $compress ) {
			$gz_diskfile = "{$diskfile}.gz";

			/**
			 * Try upping the memory limit before gzipping
			 */
			if ( function_exists( 'memory_get_usage' ) && ( (int) @ini_get( 'memory_limit' ) < 64 ) ) {
				@ini_set( 'memory_limit', '64M' );
			}

			if ( file_exists( $diskfile ) ) {
				/**
				 * Try gzipping with an external application
				 */
				if ( ! file_exists( $gz_diskfile ) ) {
					@exec( "gzip $diskfile" );
				}

				if ( file_exists( $gz_diskfile ) ) {

					unlink( $diskfile );

					$diskfile = $gz_diskfile;
					$filename = "{$filename}.gz";

					/**
					 * Try to compress to gzip, if available
					 */
				} else {
					if ( function_exists( 'gzencode' ) ) {
						if ( function_exists( 'file_get_contents' ) ) {
							$text = file_get_contents( $diskfile );
						} else {
							$text = implode( '', file( $diskfile ) );
						}
						$gz_text = gzencode( $text, 9 );
						$fp      = fopen( $gz_diskfile, 'w' );
						fwrite( $fp, $gz_text );
						if ( fclose( $fp ) ) {
							unlink( $diskfile );
							$diskfile = $gz_diskfile;
							$filename = "{$filename}.gz";
						}
					}
				}
				/*
				 *
				 */
			} elseif ( file_exists( $gz_diskfile ) ) {
				$diskfile = $gz_diskfile;
				$filename = "{$filename}.gz";
			}
		}

		//provide file for download
		if ( file_exists( $diskfile ) ) {
			header( 'Content-Type: application/force-download' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Length: ' . filesize( $diskfile ) );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			$success = readfile( $diskfile );
			if ( $success ) {
				unlink( $diskfile );
				die();
			}
		}

	}

	/**
	 * @return string
	 */
	public function get_backup_dir() {

		return $this->backup_dir;
	}

	/**
	 * @string  $backup_dir
	 * @param $backup_dir
	 */
	public function set_backup_dir( $backup_dir ) {

		$this->backup_dir = $backup_dir;
	}

	/**
	 * strips the current table prefix and adds a new one provided in $new_table_prefix
	 *
	 * @param $table
	 * @param $new_table_prefix
	 *
	 * @return string  The table name with new prefix
	 */
	protected function get_new_table_name( $table, $new_table_prefix ) {

		//get length of base_prefix
		$prefix        = $this->dbm->get_base_prefix();
		$prefix_length = strlen( $prefix );
		//strip old prefix
		$part_after_prefix = substr( $table, $prefix_length );

		#//build new table name
		return $new_table_prefix . $part_after_prefix;
	}


}