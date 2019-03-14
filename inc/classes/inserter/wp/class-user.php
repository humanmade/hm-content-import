<?php

namespace HMCI\Inserter\WP;

/**
 * WordPress user inserter - manages inserting users from user_data and meta_data
 *
 * @package HMCI\Inserter\WP
 */
class User extends Base {

	/**
	 * Insert a user object into the database
	 *
	 * @param $user_data
	 * @param bool $canonical_id
	 * @param array $user_meta
	 * @return int|\WP_Error
	 */
	static function insert( $user_data, $canonical_id = false, $user_meta = [] ) {

		$current_id = static::get_id_from_canonical_id( $canonical_id );

		if ( $canonical_id && $current_id ) {
			$user_data['ID'] = $current_id;
		}

		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		if ( $canonical_id ) {
			static::set_canonical_id( $user_id, $canonical_id );
		}

		if ( $user_meta && is_array( $user_meta ) ) {
			static::set_meta( $user_id, $user_meta );
		}

		return $user_id;
	}

	/**
	 * Get user ID from email address
	 *
	 * @param $email
	 * @return null|string
	 */
	static function get_id_from_email( $email ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_email = %s", $email ) );
	}

	/**
	 * Get user ID from login
	 *
	 * @param $login
	 * @return null|string
	 */
	static function get_id_from_login( $login ) {

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_login = %s", $login ) );
	}

	/**
	 * Get the WP core object type used by the inserter.
	 *
	 * @return string
	 */
	static function get_core_object_type() {

		return 'user';
	}
}
