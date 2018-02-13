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

		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return array();
		}

		$header = null;
		$data   = array();

		if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {

			while ( ( $row = fgetcsv( $handle, 4096, ',', '"' ) ) !== false ) {

				if ( ! $header ) {
					$header = $row;
				} else {
					$data[] = array_combine($header, $row);
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
