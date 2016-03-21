<?php

namespace HMCI\Importer;

abstract class XML_Files extends Files {

	public function parse_item( $item ) {

		if ( $item instanceof \SimpleXMLElement ) {
			return $item;
		}

		return simplexml_load_string( $item, 'SimpleXMLElement' );
	}

	protected function filter_files( $files ) {

		return array_filter( $files, function( $file_path ) {
			return ( strtoupper( substr( $file_path, -4 ) ) === '.XML' );
		} );
	}
}
