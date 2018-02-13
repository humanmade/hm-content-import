<?php

namespace HMCI\Inserter\WP;

/**
 * co-authors-plus guest author inserter class. Used for importing guest-author objects
 *
 * @package HMCI\Inserter\WP
 */
class Guest_Author extends Post {

	/**
	 * Add guest author (post) object into the database
	 *
	 * @param array $user_data
	 * @param bool $canonical_id
	 * @param array $author_meta
	 * @return null|string|\WP_Error
	 */
	static function insert( $user_data = [], $canonical_id = false, $author_meta = [] ) {

		$current_id = static::get_id_from_canonical_id( $canonical_id, 'guest-author' );

		if ( $canonical_id && $current_id ) {
			return $current_id;
		}

		global $coauthors_plus;

		if ( empty( $coauthors_plus->guest_authors ) ) {
			return new \WP_Error( 'The co-authors-plus plugin must be active in order to use the Guest_Author import type' );
		}

		$post_id = $coauthors_plus->guest_authors->create( $user_data );

		if ( ! $post_id ) {
			$post_id = new \WP_Error( 'Error creating guest author ' . $canonical_id );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( $canonical_id ) {
			static::set_canonical_id( $post_id, $canonical_id, 'guest-author' );
		}

		if ( $author_meta && is_array( $author_meta ) ) {
			static::set_meta( $post_id, $author_meta );
		}

		return $post_id;
	}

	/**
	 * Get guest author object by ID
	 *
	 * @param $field
	 * @param $value
	 * @return bool|int
	 */
	static function get_id_by( $field, $value ) {

		$user = static::get_object_by( $field, $value );

		return ! empty( $user['ID'] ) ? absint( $user['ID'] ) : false;
	}

	/**
	 * Get guest author object by provided field
	 *
	 * @param $user_field
	 * @param $user_value
	 * @return mixed
	 */
	static function get_object_by( $user_field, $user_value ) {

		global $coauthors_plus;

			$user = $coauthors_plus->get_coauthor_by( $user_field, $user_value );

		return $user;
	}

	/**
	 * Set authors on a provided post
	 *
	 * @param $user_field
	 * @param $user_values
	 * @param $post_id
	 * @return bool
	 */
	static function set_authors_for_post( $user_field, $user_values, $post_id ) {

		if ( ! is_array( $user_values ) ) {
			$user_values = [ $user_values ];
		}

		$logins = [];

		foreach ( $user_values as $user_value ) {

			$user = static::get_object_by( $user_field, $user_value );

			if ( ! empty( $user->user_login ) ) {
				$logins[] = $user->user_login;
			}
		}

		global $coauthors_plus, $wpdb;

		// Set the coauthors
		$coauthors = array_unique( $logins );

		$coauthor_objects = [];

		foreach ( $coauthors as &$author_name ) {

			$author             = $coauthors_plus->get_coauthor_by( 'user_login', $author_name );
			$coauthor_objects[] = $author;
			$term               = $coauthors_plus->update_author_term( $author );
			$author_name        = $term->slug;
		}

		wp_set_post_terms( $post_id, $coauthors, $coauthors_plus->coauthor_taxonomy, false );

		// If the original post_author is no longer assigned,
		// update to the first WP_User $coauthor
		$post_author_user = get_user_by( 'id', get_post( $post_id )->post_author );

		if ( empty( $post_author_user ) || ! in_array( $post_author_user->user_login, $coauthors ) ) {

			foreach ( $coauthor_objects as $coauthor_object ) {
				if ( 'wpuser' == $coauthor_object->type ) {
					$new_author = $coauthor_object;
					break;
				}
			}

			// Uh oh, no WP_Users assigned to the post
			if ( empty( $new_author ) ) {
				return false;
			}

			$wpdb->update( $wpdb->posts,
				[
					'post_author' => $new_author->ID,
				],
				[
					'ID' => $post_id,
				]
			);
			clean_post_cache( $post_id );
		}

		return true;
	}

}
