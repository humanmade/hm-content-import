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
			$comment_id = wp_update_comment( $comment_data, true );
		} else {
			$comment_id = wp_insert_comment( $comment_data, true );
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
	 * Set meta data
	 *
	 * @param $comment_id
	 * @param $meta_data
	 */
	static function set_meta( $comment_id, $meta_data ) {

		foreach ( $meta_data as $meta_key => $meta_value ) {

			if ( is_null( $meta_value ) ) {
				delete_comment_meta( $comment_id, $meta_key );
			} else {
				update_comment_meta( $comment_id, $meta_key, $meta_value );
			}
		}

	}

	/**
	 * Check if comment exists with provided canonical ID
	 *
	 * @param $canonical_id
	 * @return bool
	 */
	static function exists( $canonical_id ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id );
	}

	/**
	 * Get comment ID from canonical ID
	 *
	 * @param $canonical_id
	 * @return null|string
	 */
	static function get_id_from_canonical_id( $canonical_id ) {

		$meta_key = sprintf( 'hmci_lookup_%s', md5( $canonical_id ) );

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = %s", $meta_key ) );
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

		update_comment_meta( $id, static::get_canonical_id_key(), $canonical_id );
		update_comment_meta( $id, sprintf( 'hmci_lookup_%s', md5( $canonical_id ) ), 1 );
	}

}
