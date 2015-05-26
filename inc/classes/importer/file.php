<?php

namespace HMCI\Importer;

abstract class File extends Base {

	public function get_items( $offset, $count ) {

		$files = $this->get_files_in_path();

		if ( is_wp_error( $files ) ) {
			return $files;
		}

		$contents    = $this->get_file_contents( array_pop( $file ) );
		$items_raw   = $this->get_items_from_content( $contents );
		$items_paged = array_slice( $items_raw, $offset, $count );
		$items       = array();

		foreach ( $items_paged as $item_raw ) {

			$items[] = $this->parse_item( $item_raw );
		}

		return $items;
	}
	protected function get_file_contents( $file ) {

		return file_get_contents( $file );
	}

	abstract function get_items_from_content();
}
