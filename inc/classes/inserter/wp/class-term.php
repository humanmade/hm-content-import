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

		$current_id = static::get_id_from_canonical_id( $canonical_id, $taxonomy );

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
			$taxonomy = ! empty( $args['taxonomy'] ) ? $args['taxonomy'] : $taxonomy;
			static::set_canonical_id( $term_id, $canonical_id, $taxonomy );
		}

		// Meta data provided, set it
		if ( $term_meta && is_array( $term_meta ) ) {
			static::set_meta( $term_id, $term_meta );
		}

		return $term_id;
	}

	/**
	 * Set term meta
	 *
	 * @param $term_id
	 * @param $meta_data
	 */
	static function set_meta( $term_id, $meta_data ) {

		if ( ! is_callable( 'delete_term_meta' ) || ! is_callable( 'update_term_meta' ) ) {
			return;
		}

		foreach ( $meta_data as $meta_key => $meta_value ) {

			if ( is_null( $meta_value ) ) {
				delete_term_meta( $term_id, $meta_key );
			} else {
				update_term_meta( $term_id, $meta_key, $meta_value );
			}
		}

	}

	/**
	 * Check if term exists for provided canonical ID
	 *
	 * @param $canonical_id
	 * @param null $taxonomy
	 * @return bool
	 */
	static function exists( $canonical_id, $taxonomy = null ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id, $taxonomy );
	}

	/**
	 * Get term ID from canonical ID
	 *
	 * @param $canonical_id
	 * @param null $taxonomy
	 * @return bool|null|string
	 */
	static function get_id_from_canonical_id( $canonical_id, $taxonomy = null ) {

		if ( ! is_callable( 'delete_term_meta' ) || ! is_callable( 'update_term_meta' ) ) {
			return false;
		}

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT term_id FROM $wpdb->termmeta WHERE meta_key = %s AND meta_value = %s", static::get_canonical_id_key_suffixed( $taxonomy ), $canonical_id ) );
	}

	/**
	 * Set term canonical ID
	 *
	 * @param $id
	 * @param $canonical_id
	 * @param null $taxonomy
	 */
	static function set_canonical_id( $id, $canonical_id, $taxonomy = null ) {

		if ( ! $canonical_id || ! is_callable( 'update_term_meta' ) ) {
			return;
		}

		update_term_meta( $id, static::get_canonical_id_key_suffixed( $taxonomy ), $canonical_id );
	}

	/**
	 * Get canonical ID meta key
	 *
	 * @param null $taxonomy
	 * @return mixed|string|void
	 */
	static function get_canonical_id_key_suffixed( $taxonomy = null ) {

		return ( $taxonomy ) ? static::get_canonical_id_key() . '_' . $taxonomy : static::get_canonical_id_key();
	}

}
