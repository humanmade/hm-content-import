<?php

namespace HMCI\Inserter\WP;

/**
 * WordPress post inserter - manages inserting posts from post_data and meta_data
 *
 * @package HMCI\Inserter\WP
 */
class Post extends Base {

	/**
	 * Add post object to the database
	 *
	 * @param array $post_data
	 * @param bool $canonical_id
	 * @param array $post_meta
	 * @return int|\WP_Error
	 */
	static function insert( $post_data = [], $canonical_id = false, $post_meta = [] ) {

		if ( empty( $post_data['post_type'] ) ) {
			$post_data['post_type'] = 'post';
		}

		if ( empty( $post_data['ID'] ) && $canonical_id ) {

			$current_id = static::get_id_from_canonical_id( $canonical_id, $post_data['post_type'] );

			if ( $current_id ) {
				$post_data['ID'] = $current_id;
			}
		}

		if ( ! empty( $post_data['ID'] ) ) {
			$post_id = wp_update_post( $post_data, true );
		} else {
			$post_id = wp_insert_post( $post_data, true );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( $canonical_id ) {
			static::set_canonical_id( $post_id, $canonical_id, $post_data['post_type'] );
		}

		if ( $post_meta && is_array( $post_meta ) ) {
			static::set_meta( $post_id, $post_meta );
		}

		return $post_id;
	}

	/**
	 * Set meta data
	 *
	 * @param $post_id
	 * @param $meta_data
	 */
	static function set_meta( $post_id, $meta_data ) {

		foreach ( $meta_data as $meta_key => $meta_value ) {

			if ( is_null( $meta_value ) ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}

	}

	/**
	 * Check if post exists with provided canonical ID
	 *
	 * @param $canonical_id
	 * @param string $post_type
	 * @return bool
	 */
	static function exists( $canonical_id, $post_type = 'post' ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id, $post_type );
	}

	/**
	 * Get post ID from canonical ID
	 *
	 * @param $canonical_id
	 * @param string $post_type
	 * @return null|string
	 */
	static function get_id_from_canonical_id( $canonical_id, $post_type = 'post' ) {

		$meta_key = sprintf( 'hmci_canonical_id_' . $post_type );

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", $meta_key, $canonical_id ) );
	}

	/**
	 * Set canonical ID meta
	 *
	 * @param $id
	 * @param $canonical_id
	 * @param string $post_type
	 */
	static function set_canonical_id( $id, $canonical_id, $post_type = 'post' ) {

		if ( ! $canonical_id ) {
			return;
		}

		update_post_meta( $id, static::get_canonical_id_key() . '_' . $post_type, $canonical_id );
	}

}
