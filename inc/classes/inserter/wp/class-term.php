<?php

namespace HMCI\Inserter\WP;

/**
 * WordPress term inserter - manages inserting terms
 *
 * @package HMCI\Inserter\WP
 */
class Term extends Base {

	/**
	 * Add a term object to the database
	 *
	 * @param $term
	 * @param $taxonomy
	 * @param bool $canonical_id
	 * @param array $args
	 * @param array $term_meta
	 * @return array|bool|int|null|string|\WP_Error|\WP_Term
	 */
	static function insert( $term, $taxonomy, $canonical_id = false, $args = [], $term_meta = [] ) {

		$current_id = static::get_id_from_canonical_id( $canonical_id );

		// Got term by canonical ID marker
		if ( $canonical_id && $current_id ) {
			$term_id = $current_id;
			// Got term by name
		} else {
			$term_exists = get_term_by( 'name', $term, $taxonomy );
			$term_id     = ! empty( $term_exists->term_id ) ? $term_exists->term_id : $term_exists;
		}

		// Term already exists, update it
		if ( ! is_wp_error( $term_id ) && $term_id && $args ) {
			$term_id = wp_update_term( $term_id, $taxonomy, $args );

			// Term doesn't exist, insert it
		} elseif ( ! is_wp_error( $term_id ) && ! $term_id ) {
			$term_id = wp_insert_term( $term, $taxonomy, $args );
		}

		if ( is_wp_error( $term_id ) ) {
			return $term_id;
		}

		// Get actual term ID
		if ( ! empty( $term_id['term_id'] ) ) {
			$term_id = $term_id['term_id'];
		} elseif ( ! is_numeric( $term_id ) ) {
			return new \WP_Error( 'Unexpected term response object' );
		}

		// Canonical ID provided, set it
		if ( $canonical_id ) {
			static::set_canonical_id( $term_id, $canonical_id );
		}

		// Meta data provided, set it
		if ( $term_meta && is_array( $term_meta ) ) {
			static::set_meta( $term_id, $term_meta );
		}

		return absint( $term_id );
	}

	/**
	 * Get the WP core object type used by the inserter.
	 *
	 * @return string
	 */
	static function get_core_object_type() {

		return 'term';
	}

	/**
	 * Get term ID from canonical ID
	 *
	 * @param $canonical_id
	 * @return null|string
	 */
	static function get_id_from_canonical_id( $canonical_id ) {

		$lookup_name = sprintf( '%s_%s', static::get_canonical_id_key(), static::get_core_object_type() );

		// If we have cached meta lookup support, use that.
		if ( class_exists( 'HM\\Meta_Lookups\\Lookup' ) && Lookup::get_instance( $lookup_name ) ) {
			return Lookup::get_instance( $lookup_name )->get( $canonical_id );
		}

		// No cached meta lookup support, do a direct query.
		global $wpdb;

		$table = $wpdb->{static::get_core_object_type() . 'meta'};

		return $wpdb->get_var( $wpdb->prepare( "SELECT term_id FROM $table WHERE meta_key = %s AND meta_value = %s", static::get_canonical_id_key(), $canonical_id ) );
	}
}
