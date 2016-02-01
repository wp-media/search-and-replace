<?php # -*- coding: utf-8 -*-
namespace Inpsyde\SearchReplace;

$base_dir = dirname( __DIR__ );

$composer_file = $base_dir . '/vendor/autoload.php';

if ( file_exists( $composer_file ) )
	require_once $composer_file;

//set up autoloader

require_once $base_dir . './src/inc/Autoloader.php';
$autoloader = new inc\Autoloader( __NAMESPACE__, $base_dir.'./src/' );
$autoloader->register();


