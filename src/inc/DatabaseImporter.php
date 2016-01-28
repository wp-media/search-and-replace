<?php
/**
 * Database Importer for inpsyde search and replace plugin.
 */

namespace Inpsyde\SearchReplace\inc;

class DatabaseImporter {

	public function __construct() {}

	/**
	 * imports a sql file via mysqli
	 *
	 * @param  string   $sql
	 * @param \WP_Error $error
	 *
	 * @return int  Number of Sql queries made, -1 if error
	 */
	public function import_sql( $sql, $error ) {

		//connect via mysqli for easier db import
		$mysqli = new \mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

		// Run the SQL
		$i = 1;
		if ( $mysqli->multi_query( $sql ) ) {
			do {
				if ( $mysqli->more_results() ) {
					$mysqli->next_result();

					$i ++;
				}
			} while ( $mysqli->more_results() );
		}

		if ( $mysqli->errno ) {
			$error->add( 'sql_import_error', __( '<b>Mysqli Error:</b> ' . $mysqli->error, 'insr' ) );

			return - 1;

		}

		mysqli_close( $mysqli );

		return $i;

	}

}