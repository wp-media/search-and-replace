<?php # -*- coding: utf-8 -*-

namespace Requisite\Loader;

/**
 * Interface FileLoaderInterface
 *
 * Loads a given file, if exists.
 *
 * @package Requisite\Loader
 */
interface FileLoaderInterface {

	/**
	 * @param string $file
	 * @return bool
	 */
	public function loadFile( $file );
}