<?php

namespace HMCI\Iterator\Files;

/**
 * Base XML files iterator class
 * Iterates over provided XML files for processing
 *
 * Class XML
 * @package HMCI\Iterator\Files
 */
abstract class XML extends Base {

	/**
	 * Parse file contents to simplexml object
	 *
	 * @param $item
	 * @return bool|\SimpleXMLElement
	 */
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

	/**
	 * Filter provided files (must have .xml extension)
	 *
	 * @param $files
	 * @return array
	 */
	protected function filter_files( $files ) {

		return array_filter( $files, function( $file_path ) {
			return ( strtoupper( substr( $file_path, -4 ) ) === '.XML' );
		} );
	}
}
