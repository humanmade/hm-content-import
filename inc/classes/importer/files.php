<?php

namespace HMCI\Importer;

abstract class Files extends Base {

	use File_Trait;

	public function get_items( $offset, $count ) {

		$files       = $this->filter_files( $this->get_files_in_path() );

		if ( is_wp_error( $files ) ) {
			return $files;
		}

		$files_paged = array_slice( $files, $offset, $count );
		$items       = array();

		foreach ( $files_paged as $file_path ) {

			$items[] = $this->get_file_contents( $file_path );
		}

		return $items;
	}

	public function get_count() {

		$files_in_path = $this->get_files_in_path();

		return is_wp_error( $files_in_path ) ? $files_in_path : count( $files_in_path );
	}

	protected function get_file_contents( $file ) {

		return file_get_contents( $file );
	}

	abstract protected function filter_files( $files );
}
