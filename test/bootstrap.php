<?php # -*- coding: utf-8 -*-
namespace Inpsyde\SearchReplace;

require_once 'vendor/autoload.php';

/**
 * Load the Requisite library. Alternatively you can use composer's
 */
require_once 'inc/requisite/src/Requisite/Requisite.php';
\Requisite\Requisite::init();

$autoloader = new \Requisite\SPLAutoLoader;
$autoloader->addRule(
	new \Requisite\Rule\Psr4(
		__DIR__ . '/../inc',       // base directory
		'Inpsyde\SearchReplace' // base namespace
	)
);