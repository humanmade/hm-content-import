<?php

namespace HMCI\Source;

trait CSV_File {

	use File;

	public function get_items( $offset, $count ) {

		$files = $this->get_files_in_path();

		if ( is_wp_error( $files ) ) {
			return $files;
		}

		$contents    = $this->get_file_contents( array_pop( $files ) );
		$items_raw   = $this->get_items_from_content( $contents );

		$items_paged = array_slice( $items_raw, $offset, $count );
		$items       = array();

		foreach ( $items_paged as $item_raw ) {

			$items[] = $this->parse_item( $item_raw );
		}

		return $items;
	}

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

	protected function filter_files( $files ) {

		return array_filter( $files, function( $filename ) {

			return strtoupper( substr( $filename, -4 ) ) === '.CSV';

		} );
	}
	
}
