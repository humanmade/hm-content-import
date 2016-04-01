<?php

namespace HMCI\Import_Type;

class Post extends Base {

	static function insert( $post_data = array(), $canonical_id = false, $post_meta = array() ) {

		if ( empty( $post_data['post_type'] ) ) {
			$post_data['post_type'] = 'post';
		}

        if ( $canonical_id && $current_id = static::get_id_from_canonical_id( $canonical_id, $post_data['post_type'] ) ) {
			$post_data['ID'] = $current_id;
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

	static function set_meta( $post_id, $meta_data ) {

		foreach ( $meta_data as $meta_key => $meta_value ) {

			if ( is_null( $meta_value ) ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}

	}

	static function exists( $canonical_id, $post_type = 'post' ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id, $post_type );
	}

	static function get_id_from_canonical_id( $canonical_id, $post_type = 'post' ) {

		$meta_key = sprintf( 'hmci_lookup_%s', md5( $post_type . $canonical_id ) );

		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s", $meta_key ) );
	}

	static function set_canonical_id( $id, $canonical_id, $post_type = 'post' ) {

		if ( ! $canonical_id ) {
			return;
		}

		update_post_meta( $id, static::get_canonical_id_key() . '_' . $post_type , $canonical_id );
		update_post_meta( $id, sprintf( 'hmci_lookup_%s', md5( $post_type . $canonical_id ) ), 1 );
	}

}
