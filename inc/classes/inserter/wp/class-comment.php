<?php

namespace HMCI\Inserter\WP;

/**
 * WordPress comment inserter - manages inserting comments from comment_data and meta_data
 *
 * @package HMCI\Inserter\WP
 */
class Comment extends Base {

	/**
	 * Add comment object to the database
	 *
	 * @param array $comment_data
	 * @param bool $canonical_id
	 * @param array $comment_meta
	 * @return int|\WP_Error
	 */
	static function insert( $comment_data = [], $canonical_id = false, $comment_meta = [] ) {

		$current_id = static::get_id_from_canonical_id( $canonical_id );

		if ( empty( $comment_data['comment_ID'] ) && $canonical_id && $current_id ) {
			$comment_data['comment_ID'] = $current_id;
		}

		if ( ! empty( $comment_data['comment_ID'] ) ) {
			$comment_id = wp_update_comment( $comment_data );
		} else {
			$comment_id = wp_insert_comment( $comment_data );
		}

		if ( is_wp_error( $comment_id ) ) {
			return $comment_id;
		}

		if ( $canonical_id ) {
			static::set_canonical_id( $comment_id, $canonical_id );
		}

		if ( $comment_meta && is_array( $comment_meta ) ) {
			static::set_meta( $comment_id, $comment_meta );
		}

		return $comment_id;
	}

	/**
	 * Get the WP core object type used by the inserter.
	 *
	 * @return string
	 */
	static function get_core_object_type() {

		return 'comment';
	}
}
