<?php

namespace HMCI\Source;

trait File {

	use Files;

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

	public function get_items_from_content( $file ) {
		return $file;
	}
}
