<?php

namespace HMCI\Iterator\File;

/**
 * Base JSON file iterator class
 *
 * Iterates over provided JSON File for processing
 *
 * Class JSON
 * @package HMCI\Iterator\File
 */
abstract class JSON extends Base {

	/**
	 * Get file contents
	 *
	 * @param $file
	 * @return array|mixed|object
	 */
	protected function get_file_contents( $file ) {

		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return [];
		}

		$json = file_get_contents( $file );

		return json_decode( $json );
	}

	/**
	 * Filter files (only process files with .JSON extension)
	 *
	 * @param $files
	 * @return array
	 */
	protected function filter_files( $files ) {

		return array_filter( $files, function( $filename ) {

			return strtoupper( substr( $filename, -4 ) ) === '.JSON';

		} );
	}

}
