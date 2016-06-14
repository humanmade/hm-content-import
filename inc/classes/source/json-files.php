<?php

namespace HMCI\Source;

trait JSON_Files {

	use Files;

	public function parse_item( $item ) {

		if ( is_array( $item ) ) {
			return $item;
		}

		return json_decode( $item, true );
	}

	protected function filter_files( $files ) {

		return array_filter( $files, function( $file_path ) {
			return ( strtoupper( substr( $file_path, -5 ) ) === '.JSON' );
		} );
	}
}
