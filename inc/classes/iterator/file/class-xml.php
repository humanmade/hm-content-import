<?php

namespace HMCI\Iterator\File;

/**
 * Base XML file iterator class
 *
 * Iterates over provided JSON File for processing
 *
 * Class XML
 * @package HMCI\Iterator\File
 */
abstract class XML extends Base {

	/**
	 * Get file contents
	 *
	 * @param $file
	 * @return array|mixed|object
	 */
	protected function get_file_contents( $file ) {

		$contents = file_get_contents( $file );

		if ( ! $contents ) {
			return [];
		}

		return simplexml_load_string( $contents );
	}

	/**
	 * Filter files (only process files with .JSON extension)
	 *
	 * @param $files
	 * @return array
	 */
	protected function filter_files( $files ) {

		return array_filter( $files, function( $filename ) {

			return strtoupper( substr( $filename, -4 ) ) === '.XML';

		} );
	}

}
