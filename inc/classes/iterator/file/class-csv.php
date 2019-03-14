<?php

namespace HMCI\Iterator\File;

/**
 * Base CSV file iterator class
 *
 * Iterates over provided csv file for processing
 *
 * Class CSV
 * @package HMCI\Iterator\File
 */
abstract class CSV extends Base {

	/**
	 * Get file contents
	 *
	 * @param $file
	 * @return array
	 */
	protected function get_file_contents( $file ) {

		$header = null;
		$data   = [];
		$handle = fopen( $file, 'r' );

		if ( $handle !== false ) {

			while ( ( $row = fgetcsv( $handle, 4096, ',', '"' ) ) !== false ) { //phpcs:ignore

				if ( ! $header ) {
					$header = $row;
				} else {
					$data[] = array_combine( $header, $row );
				}
			}

			fclose( $handle );

		}

		return $data;
	}

	/**
	 * Filter files (only allow .csv extensions)
	 *
	 * @param $files
	 * @return array
	 */
	protected function filter_files( $files ) {

		return array_filter( $files, function( $filename ) {

			return strtoupper( substr( $filename, -4 ) ) === '.CSV';

		} );
	}
}
