<?php

namespace HMCI\Destination;

/**
 *
 * Interface for Destination\Base
 *
 * Defines abstract static methods required on implementation of the Base destination class
 *
 * @package HMCI\Destination
 */
interface Base_Interface {

	/**
	 * @param $canonical_id
	 * @return mixed
	 */
	static function exists( $canonical_id );

	/**
	 * @param $item
	 * @param $canonical_id
	 * @return mixed
	 */
	static function insert( $item, $canonical_id );

	/**
	 * @param $wordpress_id
	 * @param $meta_data
	 */
	static function set_meta( $wordpress_id, $meta_data );

	/**
	 * @param $wordpress_id
	 * @param $canonical_id
	 */
	static function set_canonical_id( $wordpress_id, $canonical_id );

	/**
	 * @param $canonical_id
	 * @return mixed
	 */
	static function get_id_from_canonical_id( $canonical_id );

	/**
	 * @return mixed
	 */
	static function get_canonical_id_key();
}
