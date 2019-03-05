<?php

namespace HMCI\Iterator\Files;

/**
 * Base Files iterator class
 *
 * Iterates over provided files for processing
 *
 * Class Base
 * @package HMCI\Iterator\Files
 */
abstract class Base extends \HMCI\Iterator\Base {

	/**
	 * Get contents from files (paged)
	 *
	 * @param $offset
	 * @param $count
	 * @return array
	 * @throws \Exception
	 */
	public function get_items( $offset, $count ) {

		$files = $this->filter_files( $this->get_files_in_path() );

		if ( is_wp_error( $files ) ) {
			return $files;
		}

		$files_paged = array_slice( $files, $offset, $count );
		$items       = [];

		foreach ( $files_paged as $file_path ) {

			$items[] = $this->get_file_contents( $file_path );
		}

		return $items;
	}

	/**
	 * Get item count (number of files)
	 *
	 * @return array|int
	 * @throws \Exception
	 */
	public function get_count() {

		$files_in_path = $this->get_files_in_path();

		return is_wp_error( $files_in_path ) ? $files_in_path : count( $this->filter_files( $files_in_path ) );
	}

	/**
	 * Get contents for a file
	 *
	 * @param $file
	 * @return string
	 */
	protected function get_file_contents( $file ) {

		return file_get_contents( $file );
	}

	/**
	 * Get files in a provided path
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function get_files_in_path() {

		$path        = $this->args['export_path'];
		$check_paths = [ $path, ABSPATH . '/' . $path, ABSPATH . '../' . $path ];
		$path_found  = '';

		foreach ( $check_paths as $path ) {

			if ( file_exists( $path ) ) {
				$path_found = $path;
			}
		}

		if ( ! $path_found ) {
			// translators: %s variable references a list of comma separated file paths
			throw new \Exception( sprintf( __( 'Path not found. Attempted paths: %s', 'hmci' ), implode( ', ', $check_paths ) ), 0 );
		}

		if ( is_dir( $path_found ) ) {

			$files = array_map( function( $item ) use ( $path_found ) {

				return $path_found . '/' . $item;

			}, scandir( $path_found ) );

		} else {

			$files = [ $path_found ];
		}

		return $files;
	}

	/**
	 * Get iterator argument definitions
	 *
	 * @return array
	 */
	public static function get_iterator_args() {

		return [
			'export_path' => [
				'required'    => true,
				'type'        => 'string',
				'description' => __( 'Export path, either absolute path or relative ABSPATH', 'hmci' ),
			],
		];
	}

	/**
	 * Parse file contents
	 *
	 * @param $item
	 * @return mixed
	 */
	public function parse_item( $item ) {

		return $item;
	}

	/**
	 * Filter files
	 *
	 * @param $files
	 * @return mixed
	 */
	abstract protected function filter_files( $files );
}
