<?php # -*- coding: utf-8 -*-

$base_dir = dirname( __DIR__ );

$composer_file = $base_dir . '/vendor/autoload.php';

if ( file_exists( $composer_file ) )
	require_once $composer_file;

require_once $base_dir . '/inc/Autoloader.php';
require_once $base_dir . '/inc/Replace.php';
