<?php
/**
 * Handles export of DB
 */

namespace Inpsyde\SearchReplace\inc;

class DatabaseExporter {

	/**
	 * @array Stores all error messages
	 */
	protected $errors = array();
	/**
	 * @string  The Path to the Backup Directory
	 */
	protected $backup_dir;

	public function  __construct__() {

		$this->backup_dir = get_temp_dir();
		echo get_temp_dir();
	}

	/**
	 * Taken partially from phpMyAdmin and partially from
	 * Alain Wolf, Zurich - Switzerland
	 * Website: http://restkultur.ch/personal/wolf/scripts/db_backup/
	 * Modified by Scott Merrill (http://www.skippy.net/)
	 * to use the WordPress $wpdb object
	 *
	 * @param string $table
	 * @param string $segment
	 *
	 * @return void
	 */
	function backup_table( $table, $segment = 'none' ) {

		global $wpdb;

		$table_structure = $wpdb->get_results( "DESCRIBE $table" );
		if ( ! $table_structure ) {
			$this->error( __( 'Error getting table details', 'wp-db-backup' ) . ": $table" );

			return;
		}

		if ( ( $segment == 'none' ) || ( $segment == 0 ) ) {
			// Add SQL statement to drop existing table
			$this->stow( "\n\n" );
			$this->stow( "#\n" );
			$this->stow( "# " . sprintf( __( 'Delete any existing table %s', 'wp-db-backup' ),
			                             $this->backquote( $table ) ) . "\n" );
			$this->stow( "#\n" );
			$this->stow( "\n" );
			$this->stow( "DROP TABLE IF EXISTS " . $this->backquote( $table ) . ";\n" );

			// Table structure
			// Comment in SQL-file
			$this->stow( "\n\n" );
			$this->stow( "#\n" );
			$this->stow( "# " . sprintf( __( 'Table structure of table %s', 'wp-db-backup' ),
			                             $this->backquote( $table ) ) . "\n" );
			$this->stow( "#\n" );
			$this->stow( "\n" );

			$create_table = $wpdb->get_results( "SHOW CREATE TABLE $table", ARRAY_N );
			if ( FALSE === $create_table ) {
				$err_msg = sprintf( __( 'Error with SHOW CREATE TABLE for %s.', 'wp-db-backup' ), $table );
				$this->error( $err_msg );
				$this->stow( "#\n# $err_msg\n#\n" );
			}
			$this->stow( $create_table[ 0 ][ 1 ] . ' ;' );

			if ( FALSE === $table_structure ) {
				$err_msg = sprintf( __( 'Error getting table structure of %s', 'wp-db-backup' ), $table );
				$this->error( $err_msg );
				$this->stow( "#\n# $err_msg\n#\n" );
			}

			// Comment in SQL-file
			$this->stow( "\n\n" );
			$this->stow( "#\n" );
			$this->stow( '# ' . sprintf( __( 'Data contents of table %s', 'wp-db-backup' ),
			                             $this->backquote( $table ) ) . "\n" );
			$this->stow( "#\n" );
		}

		if ( ( $segment == 'none' ) || ( $segment >= 0 ) ) {
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

			// Batch by $row_inc

			if ( $segment == 'none' ) {
				$row_start = 0;
				$row_inc   = ROWS_PER_SEGMENT;
			} else {
				$row_start = $segment * ROWS_PER_SEGMENT;
				$row_inc   = ROWS_PER_SEGMENT;
			}

			do {
				// don't include extra stuff, if so requested
				//TODO: Check if this might be useful later on
				$where = '';
				/*$excs = (array) get_option('wp_db_backup_excs');

				if ( is_array($excs['spam'] ) && in_array($table, $excs['spam']) ) {
					$where = ' WHERE comment_approved != "spam"';
				} elseif ( is_array($excs['revisions'] ) && in_array($table, $excs['revisions']) ) {
					$where = ' WHERE post_type != "revision"';
				}

				if ( !ini_get('safe_mode')) @set_time_limit(15*60);*/
				$table_data = $wpdb->get_results( "SELECT * FROM $table $where LIMIT {$row_start}, {$row_inc}",
				                                  ARRAY_A );

				$entries = 'INSERT INTO ' . $this->backquote( $table ) . ' VALUES (';
				//    \x08\\x09, not required
				$search  = array( "\x00", "\x0a", "\x0d", "\x1a" );
				$replace = array( '\0', '\n', '\r', '\Z' );
				if ( $table_data ) {
					foreach ( $table_data as $row ) {
						$values = array();
						foreach ( $row as $key => $value ) {
							if ( $ints[ strtolower( $key ) ] ) {
								// make sure there are no blank spots in the insert syntax,
								// yet try to avoid quotation marks around integers
								$value    = ( NULL === $value || '' === $value ) ? $defs[ strtolower( $key ) ] : $value;
								$values[] = ( '' === $value ) ? "''" : $value;
							} else {
								$values[] = "'" . str_replace( $search, $replace,
								                               $this->sql_addslashes( $value ) ) . "'";
							}
						}
						$this->stow( " \n" . $entries . implode( ', ', $values ) . ');' );
					}
					$row_start += $row_inc;
				}
			} while ( ( count( $table_data ) > 0 ) and ( $segment == 'none' ) );
		}

		if ( ( $segment == 'none' ) || ( $segment < 0 ) ) {
			// Create footer/closing comment in SQL-file
			$this->stow( "\n" );
			$this->stow( "#\n" );
			$this->stow( "# " . sprintf( __( 'End of data contents of table %s', 'wp-db-backup' ),
			                             $this->backquote( $table ) ) . "\n" );
			$this->stow( "# --------------------------------------------------------\n" );
			$this->stow( "\n" );
		}
	} // end backup_table()

	/**
	 * Better addslashes for SQL queries.
	 * Taken from phpMyAdmin.
	 */
	function sql_addslashes( $a_string = '', $is_like = FALSE ) {

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
	function backquote( $a_name ) {

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

	function open( $filename = '', $mode = 'w' ) {

		if ( '' == $filename ) {
			return FALSE;
		}
		$fp = @fopen( $filename, $mode );

		return $fp;
	}

	function close( $fp ) {

		fclose( $fp );
	}

	/**
	 * Write to the backup file
	 *
	 * @param string $query_line the line to write
	 *
	 * @return null
	 */
	function stow( $query_line ) {

		if ( FALSE === @fwrite( $this->fp, $query_line ) ) {
			$this->error( __( 'There was an error writing a line to the backup script:',
			                  'wp-db-backup' ) . '  ' . $query_line . '  ' . $php_errormsg );
		}
	}

	/**
	 * Logs any error messages
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	function error( $args = array() ) {

		if ( is_string( $args ) ) {
			$args = array( 'msg' => $args );
		}
		$args                              = array_merge( array( 'loc' => 'main', 'kind' => 'warn', 'msg' => '' ),
		                                                  $args );
		$this->errors[ $args[ 'kind' ] ][] = $args[ 'msg' ];

		return TRUE;
	}

	function backup_fragment( $table, $segment, $filename ) {

		global $table_prefix, $wpdb;

		echo "$table:$segment:$filename";

		if ( $table == '' ) {
			$msg = __( 'Creating backup file...', 'wp-db-backup' );
		} else {
			if ( $segment == - 1 ) {
				$msg = sprintf( __( 'Finished backing up table \\"%s\\".', 'wp-db-backup' ), $table );
			} else {
				$msg = sprintf( __( 'Backing up table \\"%s\\"...', 'wp-db-backup' ), $table );
			}
		}

		if ( is_writable( $this->backup_dir ) ) {
			$this->fp = $this->open( $this->backup_dir . $filename, 'a' );
			if ( ! $this->fp ) {
				$this->error( __( 'Could not open the backup file for writing!', 'wp-db-backup' ) );
				$this->error( array(
					              'loc'  => 'frame',
					              'kind' => 'fatal',
					              'msg'  => __( 'The backup file could not be saved.  Please check the permissions for writing to your backup directory and try again.',
					                            'wp-db-backup' )
				              ) );
			} else {
				if ( $table == '' ) {
					//Begin new backup of MySql
					$this->stow( "# " . __( 'WordPress MySQL database backup', 'wp-db-backup' ) . "\n" );
					$this->stow( "#\n" );
					$this->stow( "# " . sprintf( __( 'Generated: %s', 'wp-db-backup' ),
					                             date( "l j. F Y H:i T" ) ) . "\n" );
					$this->stow( "# " . sprintf( __( 'Hostname: %s', 'wp-db-backup' ), DB_HOST ) . "\n" );
					$this->stow( "# " . sprintf( __( 'Database: %s', 'wp-db-backup' ),
					                             $this->backquote( DB_NAME ) ) . "\n" );
					$this->stow( "# --------------------------------------------------------\n" );
				} else {
					if ( $segment == 0 ) {
						// Increase script execution time-limit to 15 min for every table.
						if ( ! ini_get( 'safe_mode' ) ) {
							@set_time_limit( 15 * 60 );
						}
						// Create the SQL statements
						$this->stow( "# --------------------------------------------------------\n" );
						$this->stow( "# " . sprintf( __( 'Table: %s', 'wp-db-backup' ),
						                             $this->backquote( $table ) ) . "\n" );
						$this->stow( "# --------------------------------------------------------\n" );
					}
					$this->backup_table( $table, $segment );
				}
			}
		} else {
			$this->error( array(
				              'kind' => 'fatal',
				              'loc'  => 'frame',
				              'msg'  => __( 'The backup directory is not writeable!  Please check the permissions for writing to your backup directory and try again.',
				                            'wp-db-backup' )
			              ) );
		}

		if ( $this->fp ) {
			$this->close( $this->fp );
		}

		$this->error_display( 'frame' );

		echo '<script type="text/javascript"><!--//
		var msg = "' . $msg . '";
		window.parent.setProgress(msg);
		window.parent.nextStep();
		//--></script>
		';
		die();
	}

	function db_backup( $tables ) {

		global $table_prefix, $wpdb;

		$table_prefix          = ( isset( $table_prefix ) ) ? $table_prefix : $wpdb->prefix;
		$datum                 = date( "Ymd_B" );
		$this->backup_filename = DB_NAME . "_$table_prefix$datum.sql";

		if ( is_writable( $this->backup_dir ) ) {
			$this->fp = $this->open( $this->backup_dir . $this->backup_filename );
			if ( ! $this->fp ) {
				$this->error( __( 'Could not open the backup file for writing!', 'wp-db-backup' ) );

				return FALSE;
			}
		} else {
			$this->error( __( 'The backup directory is not writeable!', 'wp-db-backup' ) );

			return FALSE;
		}

		//Begin new backup of MySql
		$this->stow( "# " . __( 'WordPress MySQL database backup', 'wp-db-backup' ) . "\n" );
		$this->stow( "#\n" );
		$this->stow( "# " . sprintf( __( 'Generated: %s', 'wp-db-backup' ), date( "l j. F Y H:i T" ) ) . "\n" );
		$this->stow( "# " . sprintf( __( 'Hostname: %s', 'wp-db-backup' ), DB_HOST ) . "\n" );
		$this->stow( "# " . sprintf( __( 'Database: %s', 'wp-db-backup' ), $this->backquote( DB_NAME ) ) . "\n" );
		$this->stow( "# --------------------------------------------------------\n" );

		foreach ( $tables as $table ) {
			// Increase script execution time-limit to 15 min for every table.
			if ( ! ini_get( 'safe_mode' ) ) {
				@set_time_limit( 15 * 60 );
			}
			// Create the SQL statements
			$this->stow( "# --------------------------------------------------------\n" );
			$this->stow( "# " . sprintf( __( 'Table: %s', 'wp-db-backup' ), $this->backquote( $table ) ) . "\n" );
			$this->stow( "# --------------------------------------------------------\n" );
			$this->backup_table( $table );
		}

		$this->close( $this->fp );

		if ( count( $this->errors ) ) {
			return FALSE;
		} else {
			return $this->backup_filename;
		}

	}

	/**
	 * @return string
	 */
	public function getBackupDir() {

		return $this->backup_dir;
	}

	/**
	 * @string  $backup_dir
	 */
	public function setBackupDir( $backup_dir ) {

		$this->backup_dir = $backup_dir;
	} //wp_db_backup

}