<?php
/**
 *
 */

namespace Inpsyde\SearchReplace\inc;

class SqlImportAdmin extends Admin{



	public function show_page(){
		require_once( 'templates/sql_import.php' );
	}

}