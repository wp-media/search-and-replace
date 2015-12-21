<?php
/**
 * Handles export of DB
 * adapted from https://github.com/matzko/wp-db-backup
 */

namespace Inpsyde\SearchReplace\inc;

class DatabaseExporter {

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
	 * @var DatabaseManager
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

	//TODO: make a common value for exporter and replacer

	public function  __construct( Replace $replace, DatabaseManager $dbm ) {

		$this->errors = new \WP_Error();

		$this->backup_dir = get_temp_dir();
		$this->replace    = $replace;
		$this->dbm        = $dbm;

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
	 *
	 * @return array $table_report Reports the changes made to the db.
	 */

	public function backup_table( $search = '', $replace = '', $table ) {

		$table_report = array(
			'table_name' => $table,
			'rows'       => 0,
			'change'     => 0,
			'changes'    => array()

		);

		$table_structure = $this->dbm->get_table_structure( $table );
		if ( ! $table_structure ) {
			$this->errors->add( 1, __( 'Error getting table details', 'insr' ) . ": $table" );

			return $table_report;
		}

		$this->stow( "\n\n" );
		$this->stow( "#\n" );
		$this->stow( "# " . sprintf( __( 'Delete any existing table %s', 'insr' ),
		                             $this->backquote( $table ) ) . "\n" );
		$this->stow( "#\n" );
		$this->stow( "\n" );
		$this->stow( "DROP TABLE IF EXISTS " . $this->backquote( $table ) . ";\n" );

		// Table structure
		// Comment in SQL-file
		$this->stow( "\n\n" );
		$this->stow( "#\n" );
		$this->stow( "# " . sprintf( __( 'Table structure of table %s', 'insr' ),
		                             $this->backquote( $table ) ) . "\n" );
		$this->stow( "#\n" );
		$this->stow( "\n" );

		$create_table = $this->dbm->get_create_table_statement( $table );
		if ( $create_table === FALSE ) {
			$err_msg = sprintf( __( 'Error with SHOW CREATE TABLE for %s.', 'insr' ), $table );
			$this->errors->add( 2, $err_msg );
			$this->stow( "#\n# $err_msg\n#\n" );
		}
		$this->stow( $create_table[ 0 ][ 1 ] . ' ;' );

		if ( $table_structure === FALSE ) {
			$err_msg = sprintf( __( 'Error getting table structure of %s', 'insr' ), $table );
			$this->errors->add( 3, $err_msg );
			$this->stow( "#\n# $err_msg\n#\n" );
		}

		// Comment in SQL-file
		$this->stow( "\n\n" );
		$this->stow( "#\n" );
		$this->stow( '# ' . sprintf( __( 'Data contents of table %s', 'insr' ),
		                             $this->backquote( $table ) ) . "\n" );
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
				$ints[ strtolower( $struct->Field ) ] = "1";
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

			$entries = 'INSERT INTO ' . $this->backquote( $table ) . ' VALUES (';
			//    \x08\\x09, not required
			$hex_search  = array( "\x00", "\x0a", "\x0d", "\x1a" );
			$hex_replace = array( '\0', '\n', '\r', '\Z' );
			if ( $table_data ) {
				foreach ( $table_data as $row ) {
					$values = array();
					$table_report[ 'rows' ] ++;

					foreach ( $row as $column => $value ) {
						//skip replace if no search pattern
						if ( $search != '' ) {

							//check if we need to replace something
							//skip primary_key
							if ( $column != $primary_key ) {

								$edited_data = $this->replace->recursive_unserialize_replace( $search, $replace, $value );

								// Something was changed
								if ( $edited_data != $value ) {

									$table_report[ 'change' ] ++;

									// log changes

									$table_report[ 'changes' ][] = array(
										'row'    => $table_report[ 'rows' ],
										'column' => $column,
										'from'   => ( $value ),
										'to'     => ( $edited_data )
									);
									$value                       = $edited_data;
								}
							}
						}
						if ( isset ( $ints[ strtolower( $column ) ] ) ) {
							// make sure there are no blank spots in the insert syntax,
							// yet try to avoid quotation marks around integers
							$value    = ( NULL === $value || '' === $value ) ? $defs[ strtolower( $column ) ] : $value;
							$values[] = ( '' === $value ) ? "''" : $value;
						} else {
							$values[] = "'" . str_replace( $hex_search, $hex_replace,
							                               $this->sql_addslashes( $value ) ) . "'";
						}
					}
					$this->stow( " \n" . $entries . implode( ', ', $values ) . ');' );
				}

			}
		}

		// Create footer/closing comment in SQL-file
		$this->stow( "\n" );
		$this->stow( "#\n" );
		$this->stow( "# " . sprintf( __( 'End of data contents of table %s', 'insr' ),
		                             $this->backquote( $table ) ) . "\n" );
		$this->stow( "# --------------------------------------------------------\n" );
		$this->stow( "\n" );

		return $table_report;

	}

	/**
	 * Better addslashes for SQL queries.
	 * Taken from phpMyAdmin.
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
	 */
	protected function backquote( $a_name ) {

		if ( ! empty( $a_name ) && $a_name != '*' ) {
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

		if ( $filename == '' ) {
			return FALSE;
		}
		$fp = @fopen( $filename, $mode );

		return $fp;
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
			$this->errors->add( 4, __( 'There was an error writing a line to the backup script:',
			                           'insr' ) . '  ' . $query_line . '  ' . $php_errormsg );
		}
	}

	/**
	 * Write to the backup file
	 *
	 * @param string $query_line the line to write
	 *
	 * @return array $report    $report [ 'filename'] : Name of Backup file,
	 *                          $report[ 'errors'] : WP_Error_object,
	 *                          $report ['changes'] : Array with replacements in tables
	 *
	 *
	 */
	function db_backup( $search, $replace, $tables ) {

		$report = array(
			'filename' => '',
			'errors'   => NULL,
			'changes'  => array()
		);

		$table_prefix          = $this->dbm->get_base_prefix();
		$datum                 = date( "Ymd_B" );
		$this->backup_filename = DB_NAME . "_$table_prefix$datum.sql";

		if ( is_writable( $this->backup_dir ) ) {
			$this->fp = $this->open( $this->backup_dir . $this->backup_filename );
			if ( ! $this->fp ) {
				$this->errors->add( 8, __( 'Could not open the backup file for writing!', 'insr' ) );

				return $report;
			}
		} else {
			$this->errors->add( 9, __( 'The backup directory is not writeable!', 'insr' ) );

			return $report;
		}

		//Begin new backup of MySql
		$this->stow( "# " . __( 'WordPress MySQL database backup', 'insr' ) . "\n" );
		$this->stow( "#\n" );
		$this->stow( "# " . sprintf( __( 'Generated: %s', 'insr' ), date( "l j. F Y H:i T" ) ) . "\n" );
		$this->stow( "# " . sprintf( __( 'Hostname: %s', 'insr' ), DB_HOST ) . "\n" );
		$this->stow( "# " . sprintf( __( 'Database: %s', 'insr' ), $this->backquote( DB_NAME ) ) . "\n" );
		$this->stow( "# --------------------------------------------------------\n" );

		foreach ( $tables as $table ) {
			// Increase script execution time-limit to 15 min for every table.
			if ( ! ini_get( 'safe_mode' ) ) {
				@set_time_limit( 15 * 60 );
			}
			// Create the SQL statements
			$this->stow( "# --------------------------------------------------------\n" );
			$this->stow( "# " . sprintf( __( 'Table: %s', 'insr' ), $this->backquote( $table ) ) . "\n" );
			$this->stow( "# --------------------------------------------------------\n" );
			$table_report = $this->backup_table( $search, $replace, $table );

			//log changes if any
			if ( $table_report[ 'change' ] != 0 ) {
				$report[ 'changes' ][ $table ] = $table_report;
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
	 * @param string $filename The name of the file to be downloaded
	 * @param bool   $compress If TRUE, gz compression is used
	 *
	 * @return bool TRUE if delivery was successful
	 */
	function deliver_backup( $filename = '', $compress = FALSE ) {

		if ( $filename == '' ) {
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

			if ( file_exists( $diskfile ) && empty( $_GET[ 'download-retry' ] ) ) {
				/**
				 * Try gzipping with an external application
				 */
				if ( file_exists( $diskfile ) && ! file_exists( $gz_diskfile ) ) {
					@exec( "gzip $diskfile" );
				}

				if ( file_exists( $gz_diskfile ) ) {
					if ( file_exists( $diskfile ) ) {
						unlink( $diskfile );
					}
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
							$text = implode( "", file( $diskfile ) );
						}
						$gz_text = gzencode( $text, 9 );
						$fp      = fopen( $gz_diskfile, "w" );
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
			} elseif ( file_exists( $gz_diskfile ) && empty( $_GET[ 'download-retry' ] ) ) {
				$diskfile = $gz_diskfile;
				$filename = "{$filename}.gz";
			}
		}

		//provide file for download
		if ( file_exists( $diskfile ) ) {
			header( "Content-Type: application/force-download" );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Length: ' . filesize( $diskfile ) );
			header( "Content-Disposition: attachment; filename=$filename" );
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
	 */
	public function set_backup_dir( $backup_dir ) {

		$this->backup_dir = $backup_dir;
	}

}