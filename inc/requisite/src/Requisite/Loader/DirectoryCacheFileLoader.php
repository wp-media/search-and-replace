<?php # -*- coding: utf-8 -*-

namespace Requisite\Loader;

/**
 * Class DirectoryCacheFileLoader
 *
 * reads in a whole directory at once to cache the existing files
 * to avoid frequently usage of file_exists() checks
 *
 * @package Requisite\Loader
 */
class DirectoryCacheFileLoader implements FileLoaderInterface {

	/**
	 * @type array
	 */
	private $files = array();

	/**
	 * @type string
	 */
	private $extension;

	/**
	 * @tpye string
	 */
	private $base_dir;

	/**
	 * @param string $base_dir
	 * @param string $extension
	 */
	function __construct( $base_dir, $extension = '.php' ) {

		$this->base_dir  = (string) $base_dir;
		$this->extension = (string) $extension;
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	public function loadFile( $file ) {

		if ( empty( $this->files ) )
			$this->files = $this->readDirRecursive(
				$this->base_dir,
				'*' . $this->extension
			);

		if ( ! in_array( $file, $this->files ) )
			return FALSE;

		require_once $file;
		return TRUE;
	}

	/**
	 * read the subdirectory recursive and catch all files with the
	 * given pattern
	 *
	 * Will return an array with the pattern as element if no file exists
	 *
	 * @param $dir
	 * @param $pattern
	 * @return array
	 */
	public function readDirRecursive( $dir, $pattern ) {

		$sub_dirs = glob( $dir . '/*', \GLOB_ONLYDIR );
		$files = array();
		if ( ! empty( $sub_dirs ) ) {
			foreach ( $sub_dirs as $sub_dir ) {
				$files = array_merge( $files, $this->readDirRecursive( $sub_dir, $pattern ) );
			}
		}
		$files = array_merge( $files, glob( $dir . '/' . $pattern, \GLOB_NOCHECK ) );

		return $files;
	}
} 