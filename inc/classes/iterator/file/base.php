<?php

namespace HMCI\Iterator\File;

/**
 * Base files iterator class
 *
 * Iterates over provided file data for processing
 *
 * Class Base
 * @package HMCI\Iterator\File
 */
abstract class Base extends \HMCI\Iterator\Files\Base {

	/**
	 * Get items from file contents (paged)
	 *
	 * @param $offset
	 * @param $count
	 * @return array
	 * @throws \Exception
	 */
	public function get_items( $offset, $count ) {

		$files = $this->get_files_in_path();

		if ( is_wp_error( $files ) ) {
			return $files;
		}

		$contents    = $this->get_file_contents( array_pop( $files ) );
		$items_raw   = $this->get_items_from_content( $contents );

		$items_paged = array_slice( $items_raw, $offset, $count );

		return $items_paged;
	}

	/**
	 * Get items from file contents
	 *
	 * @param $file
	 * @return mixed
	 */
	public function get_items_from_content( $file ) {
		return $file;
	}

	/**
	 * Get item count in file
	 *
	 * @return int
	 */
	public function get_count() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {
			$offset += count( $items );
		}

		return $offset;
	}
}
