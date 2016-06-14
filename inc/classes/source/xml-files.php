<?php

namespace HMCI\Source;

trait XML_Files {

	use Files;

	public function parse_item( $item ) {

		if ( ! $item ) {
			return false;
		}

		if ( $item instanceof \SimpleXMLElement ) {
			return $item;
		}

		$xml = simplexml_load_string( $item, 'SimpleXMLElement' );

		return $xml;
	}

	protected function filter_files( $files ) {

		return array_filter( $files, function( $file_path ) {
			return ( strtoupper( substr( $file_path, -4 ) ) === '.XML' );
		} );
	}
}
