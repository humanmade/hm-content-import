<?php

namespace HMCI\Import_Type;

class User extends Base {

	static function insert( $user_data, $canonical_id = false ) {

		if ( $canonical_id && static::exists( $canonical_id ) ) {
			$user_data['ID'] = $canonical_id;
		}

		$user_id = wp_insert_user( $user_data, true );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		if ( $canonical_id ) {
			static::set_canonical_id( $user_id, $canonical_id );
		}

		return $user_id;
	}

	static function set_meta( $user_id, $meta_data ) {

		foreach ( $meta_data as $meta_key => $meta_value ) {

			update_user_meta( $user_id, $meta_key, $meta_value );
		}

	}

	static function get_id_from_canonical_id( $canonical_id ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value = %s", static::get_canonical_id_key(), $canonical_id ) );
	}

	static function set_canonical_id( $id, $canonical_id ) {

		if ( ! $canonical_id ) {
			return;
		}

		update_user_meta( $id, static::get_canonical_id_key(), $canonical_id );
	}

}
