<?php
namespace Inpsyde\SearchReplace\inc;

class Init {

	/**
	 * @param string $file: The path to the Plugin main file
	 */
	public function run($file) {

		//Defines the path to the main plugin directory.
		$plugin_dir_url = plugin_dir_url($file );
		define( 'INSR_DIR', $plugin_dir_url );

		//set up objects
		$dbm     = new DatabaseManager();

		$replace = new Replace( $dbm );
		$dbe = new DatabaseExporter($replace,$dbm);
		$admin = new Admin( $dbm, $dbe,$replace );




	}

}