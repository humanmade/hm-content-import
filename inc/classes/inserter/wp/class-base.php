<?php

namespace HMCI\Inserter\WP;

use HM\Meta_Lookups\Lookup;

/**
 * Base WP Inserter class
 *
 * @package HMCI\Inserter\WP
 */
abstract class Base extends \HMCI\Inserter\Base implements Base_Interface {

	/**
	 * Set meta data
	 *
	 * @param $object_id
	 * @param $meta_data
	 */
	static function set_meta( $object_id, $meta_data ) {

		foreach ( $meta_data as $meta_key => $meta_value ) {
			if ( is_null( $meta_value ) ) {
				delete_metadata( static::get_core_object_type(), $object_id, $meta_key );
			} else {
				update_metadata( static::get_core_object_type(), $object_id, $meta_key, $meta_value );
			}
		}
	}

	/**
	 * Check if post exists with provided canonical ID
	 *
	 * @param mixed  $canonical_id
	 * @return bool
	 */
	static function exists( $canonical_id ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id );
	}

	/**
	 * Get post ID from canonical ID
	 *
	 * @param $canonical_id
	 * @return null|string
	 */
	static function get_id_from_canonical_id( $canonical_id, $id_field = 'post_id' ) {
		// No cached meta lookup support, do a direct query.
		global $wpdb;

		$table = $wpdb->{static::get_core_object_type() . 'meta'};

		return $wpdb->get_var( $wpdb->prepare( "SELECT $id_field FROM $table WHERE meta_key = %s", static::get_canonical_id_key( $canonical_id ) ) );
	}

	/**
	 * Set canonical ID meta
	 *
	 * @param $id
	 * @param $canonical_id
	 */
	static function set_canonical_id( $id, $canonical_id ) {

		if ( ! $canonical_id ) {
			return;
		}

		update_metadata( static::get_core_object_type(), $id, static::get_canonical_id_key( $canonical_id ), $canonical_id );
	}
}
