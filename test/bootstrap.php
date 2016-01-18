<?php # -*- coding: utf-8 -*-

$base_dir = dirname( __DIR__ );

$composer_file = $base_dir . '/vendor/autoload.php';

if ( file_exists( $composer_file ) )
	require_once $composer_file;

require_once $base_dir . './inc/Autoloader.php';
require_once $base_dir . './inc/Replace.php';
require_once $base_dir . './inc/DatabaseExporter.php';
require_once $base_dir. './inc/DatabaseManager.php';
require_once $base_dir. './inc/Admin.php';
require_once $base_dir . './inc/DatabaseImporter.php';


// set up integration tests with wp-tests testsuite



//set path to your wp-tests path here
$tests_dir = 'D:\web\wp-tests\wordpress-dev\tests\phpunit';
define( 'WP_TESTS_DIR',$tests_dir);
define ('TEST_PLUGIN_FILE',$base_dir . './inspyde-search-replace.php');
echo TEST_PLUGIN_FILE;

require_once $tests_dir . '/includes/functions.php';
function _manually_load_plugin() {
	require TEST_PLUGIN_FILE;
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
require $tests_dir . '/includes/bootstrap.php';

activate_plugin('search-and-replace','inpsyde-search-replace.php');
echo "search & replace plugin activated \n";