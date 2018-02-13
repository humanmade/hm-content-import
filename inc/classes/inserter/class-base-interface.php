<?php

namespace HMCI\Inserter;

/**
 *
 * Interface for Inserter\Base
 *
 * Defines abstract static methods required on implementation of the Base inserter class
 *
 * @package HMCI\Inserter
 */
interface Base_Interface {

	/**
	 * Check if object exists for provided canonical ID
	 *
	 * @param $canonical_id
	 * @return mixed
	 */
	static function exists( $canonical_id );

	/**
	 * Insert object
	 *
	 * @param $item
	 * @param $canonical_id
	 * @return mixed
	 */
	static function insert( $item, $canonical_id );

	/**
	 * Set object meta
	 *
	 * @param $wordpress_id
	 * @param $meta_data
	 */
	static function set_meta( $wordpress_id, $meta_data );

	/**
	 * Set object canonical ID
	 *
	 * @param $wordpress_id
	 * @param $canonical_id
	 */
	static function set_canonical_id( $wordpress_id, $canonical_id );

	/**
	 * Get ID from canonical ID
	 *
	 * @param $canonical_id
	 * @return mixed
	 */
	static function get_id_from_canonical_id( $canonical_id );

	/**
	 * Get key used to store canonical ID
	 *
	 * @return mixed
	 */
	static function get_canonical_id_key();
}
