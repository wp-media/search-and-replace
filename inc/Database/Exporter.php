<?php /** @noinspection SqlNoDataSourceInspection */

namespace Inpsyde\SearchReplace\Database;

/**
 * Class Exporter
 *
 * @property bool|resource fb
 * @package Inpsyde\SearchReplace\Database
 */
class Exporter {

	/**
	 * Stores all error messages in a WP_Error Object
	 */
	private $errors;

	/**
	 * @string  The Path to the Backup Directory
	 */
	private $backup_dir;

	/**
	 * @var Replace
	 */
	private $replace;

	/**
	 * @var Manager
	 */
	private $dbm;

	/**
	 * Count of rows to be replaced at a time
	 *
	 * @var int
	 */
	private $page_size = 100;

	/**
	 * Stores the filename of the backup file
	 */
	private $backup_filename;

	/**
	 * Store file path.
	 *
	 * @var $fp
	 */
	private $fp;

	/**
	 * Store csv data
	 *
	 * @var array
	 */
	private $csv_data = [];

	/**
	 * Exporter constructor.
	 *
	 * @param Replace $replace
	 * @param Manager $dbm
	 * @param \WP_Error $wp_error
	 */
	public function __construct( Replace $replace, Manager $dbm, \WP_Error $wp_error ) {

		$this->errors     = $wp_error;
		$this->backup_dir = get_temp_dir();
		$this->replace    = $replace;
		$this->dbm        = $dbm;
	}

	/**
	 * Write to the backup file
	 *
	 * @param string $search
	 * @param string $replace
	 * @param array $tables The array of table names that should be exported.
	 * @param bool $domain_replace If set, exporter will change the domain name without leading http:// in table
	 *                               wp_blogs if we are on a multisite
	 * @param string $new_table_prefix
	 * @param null $csv
	 *
	 * @return array $report    $report [ 'filename'] : Name of Backup file,
	 *                          $report[ 'errors'] : WP_Error_object,
	 * $report ['changes'] : Array with replacements in tables
	 * @throws \Throwable
	 */
	public function db_backup(
		$search = '',
		$replace = '',
		$tables = [],
		$domain_replace = false,
		$new_table_prefix = '',
		$csv = null
	) {

		if ( count( $tables ) < 1 ) {
			$tables = $this->dbm->get_tables();
		}

		$report = [
			'errors'        => null,
			'changes'       => [],
			'tables'        => '0',
			'changes_count' => '0',
			'filename'      => '',
		];

		$table_prefix = $this->dbm->get_base_prefix();

		// wp_blogs needs special treatment in multisite domain replace, we need to check later if we are working on it.
		$wp_blogs_table = $table_prefix . 'blogs';

		$this->backup_filename = $new_table_prefix === '' ?
			DB_NAME . "_$table_prefix.sql" :
			DB_NAME . "_$new_table_prefix.sql";

		// If the directory for the backup isn't writable, don't proceed.
		// @ToDo Use WP_Filesystem() to access to the file system.
		if ( ! is_writable( $this->backup_dir ) ) {
			$this->errors->add( 9, esc_attr__( 'The backup directory is not writable!', 'search-and-replace' ) );

			return $report;
		}

		$this->fp = $this->open( $this->backup_dir . $this->backup_filename );

		if ( ! $this->fp ) {
			$this->errors->add(
				8,
				esc_attr__( 'Could not open the backup file for writing!', 'search-and-replace' )
			);

			return $report;
		}

		// Begin new backup of MySql get charset. if not set assume utf8.
		$charset = ( defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4' );

		$this->stow( '# ' . esc_attr__( 'WordPress MySQL database backup', 'search-and-replace' ) . "\n" );
		$this->stow( "#\n" );
		$this->stow( '# ' . sprintf( __( 'Generated: %s', 'search-and-replace' ), date( 'l j. F Y H:i T' ) ) . "\n" );
		$this->stow( '# ' . sprintf( __( 'Hostname: %s', 'search-and-replace' ), DB_HOST ) . "\n" );
		$this->stow( '# ' . sprintf( __( 'Database: %s', 'search-and-replace' ), $this->backquote( DB_NAME ) ) . "\n" );

		if ( '' !== $new_table_prefix ) {
			$this->stow(
				'# ' . sprintf(
				/* translators: $1 and $2 are the name of the database. */
					__( 'Changed table prefix: From %1$s to %2$s ', 'search-and-replace' ),
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

			// Count tables.
			$report ['tables'] ++;

			/**
			 * Check if we are replacing the domain in a multisite.
			 * If so, we replace in wp_blogs the stripped url without http(s), because the domains
			 * are stored without http://
			 */
			if ( $table === $wp_blogs_table && $domain_replace && is_multisite() ) {
				$stripped_url_search  = substr( $search, strpos( $search, '/' ) + 2 );
				$stripped_url_replace = substr( $replace, strpos( $replace, '/' ) + 2 );

				// Backup table.
				$table_report = $this->backup_table(
					$stripped_url_search,
					$stripped_url_replace,
					$table,
					$new_table_prefix
				);
			} else {
				// Backup table.
				$table_report = $this->backup_table( $search, $replace, $table, $new_table_prefix, $csv );
			}

			// Log changes if any.
			if ( 0 !== $table_report['change'] ) {
				$report['changes'][ $table ] = $table_report;

				$report ['changes_count'] += $table_report['change'];
			}
		}

		$this->close( $this->fp );

		// Return errors if any.
		if ( count( $this->errors->get_error_codes() ) ) {
			$report['errors'] = $this->errors;
		}

		$report ['filename'] = $this->backup_filename;

		return $report;
	}

	/**
	 * Open Resource
	 *
	 * @param string $filename
	 * @param string $mode
	 *
	 * @return bool|resource
	 */
	private function open( $filename = '', $mode = 'wb' ) {

		if ( '' === $filename ) {
			return false;
		}

		return @fopen( $filename, $mode );
	}

	/**
	 * writes a line to the backup file
	 *
	 * @param $query_line
	 */
	private function stow( $query_line ) {

		if ( @fwrite( $this->fp, $query_line ) === false ) {
			$this->errors->add(
				4,
				sprintf(
					esc_attr__(
						'There was an error writing a line to the backup script: %s',
						'search-and-replace'
					),
					(int) $query_line
				)
			);
		}
	}

	/**
	 * Back Quote
	 *
	 * Add backquotes to tables and db-names in
	 * SQL queries. Taken from phpMyAdmin.
	 *
	 * @param $a_name
	 *
	 * @return array|string
	 */
	private function backquote( $a_name ) {

		if ( ! empty( $a_name ) && $a_name !== '*' ) {
			if ( is_array( $a_name ) ) {
				$result = [];
				reset( $a_name );
				foreach ( $a_name as $key => $val ) {
				// while ( list( $key, $val ) = each( $a_name ) ) {
					$result[ $key ] = '`' . $val . '`';
				}

				return $result;
			}

			return '`' . $a_name . '`';
		}

		return $a_name;
	}

	/**
	 * Backup Table
	 *
	 * Taken partially from phpMyAdmin and partially from
	 * Alain Wolf, Zurich - Switzerland
	 *
	 * Website: http://restkultur.ch/personal/wolf/scripts/db_backup/
	 * Modified by Scott Merrill (http://www.skippy.net/) to use the WordPress $wpdb object
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $table
	 * @param string $new_table_prefix
	 *
	 * @return array $table_report Reports the changes made to the db.
	 * @throws \Throwable
	 */
	public function backup_table( $search = '', $replace = '', $table, $new_table_prefix = '', $csv = null ) {

		$table_report = [
			'table_name' => $table,
			'rows'       => 0,
			'change'     => 0,
			'changes'    => [],
		];

		// Default columns values.
		$defs = [];
		// Integer value container.
		$ints = [];

		// This array is storage for maybe_serialized values. We must prevent deserialization of user supplied content.
		$maybe_serialized = [];
		$binaries         = [];

		// Do we need to replace the prefix?
		$table_prefix = $this->dbm->get_base_prefix();
		$new_table    = $table;

		if ( '' !== $new_table_prefix ) {
			$new_table = $this->get_new_table_name( $table, $new_table_prefix );
		}

		// Create the SQL statements
		$this->stow( '# --------------------------------------------------------' . "\n" );
		$this->stow( '# ' . sprintf( __( 'Table: %s', 'search-and-replace' ), $this->backquote( $new_table ) ) . "\n" );

		// Retrieve table structure.
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

		if ( false === $create_table ) {
			/* translators: $1 is the name of the table */
			$err_msg = sprintf( __( 'Error with SHOW CREATE TABLE for %s.', 'search-and-replace' ), $table );
			$this->errors->add( 2, $err_msg );
			$this->stow( "#\n# $err_msg\n#\n" );
		}

		// Replace prefix if necessary
		if ( '' !== $new_table_prefix ) {
			$create_table[0][1]             = str_replace( $table, $new_table, $create_table[0][1] );
			$table_report['new_table_name'] = $new_table;
		}

		$this->stow( $create_table[0][1] . ' ;' );

		if ( false === $table_structure ) {
			/* translators: $1 is the name of the table */
			$err_msg = sprintf( __( 'Error getting table structure of %s', 'search-and-replace' ), $table );
			$this->errors->add( 3, $err_msg );
			$this->stow( "#\n# $err_msg\n#\n" );
		}

		// Comment in SQL-file
		$this->stow( "\n\n" );
		$this->stow( "#\n" );
		$this->stow(
		/* translators: $1 is the name of the new table */
			'# ' . sprintf( __( 'Data contents of table %s', 'search-and-replace' ), $this->backquote( $new_table ) ) .
			"\n"
		);
		$this->stow( "#\n" );

		foreach ( $table_structure as $struct ) {
			if ( 0 === strpos( $struct->Type, 'tinyint' )
			     || 0 === stripos( $struct->Type, 'smallint' )
			     || 0 === stripos( $struct->Type, 'mediumint' )
			     || 0 === stripos( $struct->Type, 'int' )
			     || 0 === stripos( $struct->Type, 'bigint' )
			) {
				$defs[ strtolower( $struct->Field ) ] = ( null === $struct->Default ) ? 'NULL' : $struct->Default;
				$ints[ strtolower( $struct->Field ) ] = '1';

			} elseif ( 0 === stripos( $struct->Type, 'binary' )
			           || 0 === stripos( $struct->Type, 'varbinary' )
			           || 0 === stripos( $struct->Type, 'blob' )
			           || 0 === stripos( $struct->Type, 'tinyblob' )
			           || 0 === stripos( $struct->Type, 'mediumblob' )
			           || 0 === stripos( $struct->Type, 'longblob' )
			) {
				$binaries[ strtolower( $struct->Field ) ] = 1;
			}

			// Longtext is used for meta_values as best practice in all of the automatic products.
			if ( 0 === stripos( $struct->Type, 'longtext' ) ) {
				$maybe_serialized[] = strtolower( $struct->Field );
			}
		}

		// Split columns array in primary key string and columns array.
		$columns     = $this->dbm->get_columns( $table );
		$primary_key = $columns[0];
		$row_count   = $this->dbm->get_rows( $table );
		$page_size   = $this->page_size;
		$pages       = ceil( $row_count / $page_size );

		// Prepare CSV data.
		if ( $csv !== null ) {
			$csv_lines = explode( "\n", $csv );
			$csv_head  = str_getcsv( 'search,replace' );

			foreach ( $csv_lines as $line ) {
				$this->csv_data[] = array_combine( $csv_head, str_getcsv( $line ) );
			}
		}

		for ( $page = 0; $page < $pages; $page ++ ) {
			$start = $page * $page_size;

			$table_data = $this->dbm->get_table_content( $table, $start, $page_size );

			$entries = 'INSERT INTO ' . $this->backquote( $new_table ) . ' VALUES (';
			// \x08\\x09, not required
			$hex_search  = [ "\x00", "\x0a", "\x0d", "\x1a" ];
			$hex_replace = [ '\0', '\n', '\r', '\Z' ];

			if ( $table_data ) :
				foreach ( $table_data as $row ) :
					$values = [];
					$table_report['rows'] ++;

					foreach ( $row as $column => $value ) :
						// If "change database prefix" is set we have to look for occurrences of the old prefix
						// in the db entries and change them.
						if ( $new_table !== $table ) {
							// Check if column is expected to hold serialized value.
							if ( is_serialized( $value, false )
							     && in_array( strtolower( $column ), $maybe_serialized, true )
							) {
								$value = $this->replace->recursive_unserialize_replace(
									$table_prefix,
									$new_table_prefix,
									$value
								);
							} else {
								$value = str_replace( $table_prefix, $new_table_prefix, $value );
							}
						}

						// Skip replace if no search pattern
						// Check if we need to replace something
						// Skip primary_key
						// Skip `guid` column https://codex.wordpress.org/Changing_The_Site_URL#Important_GUID_Note
						if ( $column !== $primary_key && $column !== 'guid' ) {
							// Initialize
							$edited_data = '';

							if ( '' !== $search ) {
								// Check if column is expected to hold serialized value.
								if ( is_serialized( $value, false )
								     && in_array( strtolower( $column ), $maybe_serialized, true )
								) {
									$edited_data = $this->replace->recursive_unserialize_replace(
										$search,
										$replace,
										$value
									);
								} else {
									$edited_data = str_replace( $search, $replace, $value );
								}
							}

							// If csv string has passed let's replace those values.
							if ( $csv !== null ) {
								foreach ( $this->csv_data as $entry ) {
									$edited_data = is_serialized( $edited_data, false ) ?
										$this->replace->recursive_unserialize_replace(
											$entry['search'],
											$entry['replace'],
											$edited_data
										) : str_replace( $entry['search'], $entry['replace'], $value );
								}
							}

							// When a replace happen, update the table report.
							if ( $edited_data && $edited_data !== $value ) {
								$table_report['change'] ++;

								// log changes
								$table_report['changes'][] = [
									'row'    => $table_report['rows'],
									'column' => $column,
									'from'   => $value,
									'to'     => $edited_data,
								];

								$value = $edited_data;
							}
						}

						if ( isset( $ints[ strtolower( $column ) ] ) ) {
							// make sure there are no blank spots in the insert syntax,
							// yet try to avoid quotation marks around integers
							$value    = ( null === $value || '' === $value ) ? $defs[ strtolower( $column ) ] : $value;
							$values[] = ( '' === $value ) ? "''" : $value;
						} else if ( isset( $binaries[ strtolower( $column ) ] ) ) {
							$hex      = unpack( 'H*', $value );
							$values[] = "0x$hex[1]";
						} else {
							$values[] = "'" . str_replace(
									$hex_search,
									$hex_replace,
									$this->sql_addslashes( $value )
								) . "'";
						}
					endforeach;

					$this->stow( " \n" . $entries . implode( ', ', $values ) . ');' );

				endforeach;
			endif;
		}

		// Create footer/closing comment in SQL-file
		$this->stow( "\n" );
		$this->stow( "#\n" );
		$this->stow(
			'# ' . sprintf(
			/* translators: $1 is the name of the table */
				__( 'End of data contents of table %s', 'search-and-replace' ),
				$this->backquote( $new_table )
			) . "\n"
		);
		$this->stow( "# --------------------------------------------------------\n" );
		$this->stow( "\n" );

		return $table_report;
	}

	/**
	 * Get new Table name
	 *
	 * strips the current table prefix and adds a new one provided in $new_table_prefix
	 *
	 * @param $table
	 * @param $new_table_prefix
	 *
	 * @return string  The table name with new prefix
	 */
	private function get_new_table_name( $table, $new_table_prefix ) {

		// Get length of base_prefix
		$prefix        = $this->dbm->get_base_prefix();
		$prefix_length = strlen( $prefix );
		// Strip old prefix
		$part_after_prefix = substr( $table, $prefix_length );

		// Build new table name
		return $new_table_prefix . $part_after_prefix;
	}

	/**
	 * Better addslashes for SQL queries.
	 * Taken from phpMyAdmin.
	 *
	 * @param string $a_string
	 * @param bool $is_like
	 *
	 * @return mixed
	 */
	private function sql_addslashes( $a_string = '', $is_like = false ) {

		if ( $is_like ) {
			$a_string = str_replace( '\\', '\\\\\\\\', $a_string );
		} else {
			$a_string = str_replace( '\\', '\\\\', $a_string );
		}

		return str_replace( '\'', '\\\'', $a_string );
	}

	/**
	 * Close Resource
	 *
	 * @param $fp
	 */
	private function close( $fp ) {

		fclose( $fp );
	}

	/**
	 * Deliver
	 *
	 * @param string $filename The name of the file to be downloaded.
	 * @param bool $compress If TRUE, gz compression is used.
	 *
	 * @return bool FALSE if error , has to DIE when file is delivered
	 */
	public function deliver_backup( $filename = '', $compress = false ) {

		if ( '' === $filename ) {
			return false;
		}

		// Build the file path.
		$diskfile = $this->backup_dir . $filename;

		// Let know the user why we cannot download his file.
		if ( ! file_exists( $diskfile ) ) {
			wp_die(
				esc_html__( 'Seems was not possible to create the file for some reason.', 'search-and-replace' ),
				esc_html__( 'Cannot Process the file - Search &amp; Replace', 'search-and-replace' ),
				[
					'back_link' => true,
				]
			);
		}

		// Compress file if set.
		if ( $compress ) {
			// Gzipping may eat into memory.
			$this->increase_memory();

			$diskfile = $this->gzip( $diskfile );
		}

		// Provide file for download.
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Length: ' . filesize( $diskfile ) );
		header( 'Content-Disposition: attachment; filename=' . basename( $diskfile ) );

		readfile( $diskfile );

		die();
	}

	/**
	 * Increase Memory
	 *
	 * @return void
	 */
	private function increase_memory() {

		// Try upping the memory limit before gzipping.
		if ( function_exists( 'memory_get_usage' ) && ( (int) @ini_get( 'memory_limit' ) < 64 ) ) {
			@ini_set( 'memory_limit', '64M' );
		}
	}

	/**
	 * Gzip
	 *
	 * @param string $diskfile The path of the file to compress
	 *
	 * @return string the file path compressed or not
	 */
	private function gzip( $diskfile ) {

		// The file to serve.
		$gz_diskfile = "{$diskfile}.gz";

		// Always serve a fresh file.
		// If file all-ready exists doesn't mean we have the same replace request.
		file_exists( $gz_diskfile ) && unlink( $gz_diskfile );

		// Try gzipping with an external application.
		@exec( "gzip $diskfile" );

		if ( file_exists( $gz_diskfile ) ) {
			$diskfile = $gz_diskfile;
		}

		// If we are not capable of using `gzip` command, lets try something else.
		if ( $diskfile !== $gz_diskfile && function_exists( 'gzencode' ) ) {
			$text     = file_get_contents( $diskfile );
			$gz_text  = gzencode( $text, 9 );
			$this->fb = fopen( $gz_diskfile, 'wb' );

			fwrite( $this->fp, $gz_text );

			// Don't serve gzipped file if actually we encounter problem to close it.
			if ( fclose( $this->fp ) ) {
				unlink( $diskfile );

				$diskfile = $gz_diskfile;
			}
		}

		return $diskfile;
	}
}
