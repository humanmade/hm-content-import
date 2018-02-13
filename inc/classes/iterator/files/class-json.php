<?php

namespace HMCI\Iterator\Files;

/**
 * Base JSON Files iterator class
 *
 * Iterates over provided JSON files for processing
 *
 * Class JSON
 * @package HMCI\Iterator\Files
 */
abstract class JSON extends Base {

	/**
	 * Parse file contents to JSON
	 *
	 * @param $item
	 * @return array|mixed|object
	 */
	public function parse_item( $item ) {

		return json_decode( $item, true );
	}

	/**
	 * Filter provided files (must have .json extension)
	 *
	 * @param $files
	 * @return array
	 */
	protected function filter_files( $files ) {

		return array_filter( $files, function( $file_path ) {
			return ( strtoupper( substr( $file_path, -5 ) ) === '.JSON' );
		} );
	}
}
