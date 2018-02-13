<?php

namespace HMCI\Inserter\File;

/**
 * CSV file inserter class
 *
 * CSV class instance to be used for inserting an object into a csv file
 *
 * @package HMCI\Inserter
 */
abstract class CSV extends \HMCI\Inserter\Base {

	/**
	 * Internal reference to which files have already been written in the current thread
	 *
	 * Used for inserting csv headers without explicit external control code
	 *
	 * @var array
	 */
	protected static $accessed_files = [];

	/**
	 * Insert a row into the CSV file
	 *
	 * @param array $item              Item array, can be associative
	 * @param string $file_path        Path to the file
	 * @param string $mode             Write mode for the file (default will automatically write headers then append for
	 *                                 the duration of the php process
	 * @return bool|\WP_Error
	 */
	static function insert( $item, $file_path, $mode = 'default' ) {

		$item = (array) $item;

		if ( ! in_array( $file_path, static::$accessed_files, true ) && $mode === 'default' ) {

			static::$accessed_files[] = $file_path;

			$file_input = fopen( $file_path, 'w' );

			fputcsv( $file_input, array_keys( $item ) );

		} elseif ( $mode === 'default' ) {

			$file_input = fopen( $file_path, 'a+' );

		} else {

			$file_input = fopen( $file_path, $mode );
		}

		if ( ! $file_input ) {
			return new \WP_Error( 'hmci_failed_to_open_file_socket', 'Failed to open file socket for: ' . $file_path );
		}

		fputcsv( $file_input, $item );

		fclose( $file_input );

		return true;
	}
}
