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
	 * @param array $post_data    Post data formatted as it will be saved to the posts table. Should match WP_Post data.
	 * @param bool  $canonical_id Use an existing canonical ID.
	 * @param array $post_meta    Metadata to assign to the post.
	 * @param array $options      Additional data about the post.
	 * @return int|\WP_Error
	 */
	static function insert( $post_data = [], $canonical_id = false, $post_meta = [], $options = [] ) {

		if ( empty( $post_data['post_type'] ) ) {
			$post_data['post_type'] = 'post';
		}

		if ( empty( $post_data['ID'] ) && $canonical_id ) {

			$current_id = static::get_id_from_canonical_id( $canonical_id );

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
			static::set_canonical_id( $post_id, $canonical_id );
		}

		if ( $post_meta && is_array( $post_meta ) ) {
			static::set_meta( $post_id, $post_meta );
		}

		return $post_id;
	}

	/**
	 * Get the WP core object type used by the inserter.
	 *
	 * @return string
	 */
	static function get_core_object_type() {

		return 'post';
	}
}
