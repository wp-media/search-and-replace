<?php # -*- coding: utf-8 -*-

$base_dir = dirname( __DIR__ );

$composer_file = $base_dir . '/vendor/autoload.php';

if ( file_exists( $composer_file ) )
	require_once $composer_file;

require_once $base_dir . './src/inc/Autoloader.php';
require_once $base_dir . './src/inc/Replace.php';
require_once $base_dir . './src/inc/DatabaseExporter.php';
require_once $base_dir. './src/inc/DatabaseManager.php';
require_once $base_dir. './src/inc/Admin.php';
require_once $base_dir . './src/inc/DatabaseImporter.php';

