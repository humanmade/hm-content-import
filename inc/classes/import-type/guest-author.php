<?php

namespace HMCI\Import_Type;

class Guest_Author extends Post {

	static function insert( $user_data = array(), $canonical_id = false ) {

		if ( $canonical_id && $current_id = static::get_id_from_canonical_id( $canonical_id, 'guest-author' ) ) {
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

		return $post_id;
	}
}
