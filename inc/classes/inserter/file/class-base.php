<?php

namespace HMCI\Inserter\File;

/**
 * Base file inserter class
 *
 * Standard class instance to be used for inserting an object into a file
 *
 * @package HMCI\Inserter
 */
abstract class Base extends \HMCI\Inserter\Base {

	/**
	 * Check if the item exists in the DB
	 *
	 * @param $canonical_id
	 * @return mixed
	 */
	static function exists( $canonical_id ) {
		return false;
	}

	/**
	 * Set item meta in the DB
	 *
	 * @param $wordpress_id
	 * @param $meta_data
	 * @return bool
	 */
	static function set_meta( $wordpress_id, $meta_data ) {
		return false;
	}

	/**
	 * Set the item canonical ID in the DB
	 *
	 * @param $wordpress_id
	 * @param $canonical_id
	 * @return bool
	 */
	static function set_canonical_id( $wordpress_id, $canonical_id ) {
		return false;
	}

	/**
	 * Get DB item ID from canonical ID
	 *
	 * @param $canonical_id
	 * @return mixed
	 */
	static function get_id_from_canonical_id( $canonical_id ) {
		return false;
	}

	/**
	 * Get the canonical ID meta key to be used for DB storage
	 *
	 * @return string
	 */
	static function get_canonical_id_key() {
		return false;
	}

}
