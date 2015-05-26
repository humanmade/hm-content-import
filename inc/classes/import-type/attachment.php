<?php

namespace HMCI\Import_Type;

class Attachment extends Post {

	static function insert( $path, $post_data = array(), $canonical_id = false ) {

		$post_parent = isset( $post_data['post_parent'] ) ? $post_data['post_parent'] : 0;
		$is_url      = filter_var( $path, FILTER_VALIDATE_URL );

		if ( $canonical_id && static::exists( $canonical_id ) ) {
			$post_data['ID'] = $canonical_id;
		}

		static::require_dependencies();

		$file_array = static::prepare_file( $path, $is_url );

		if ( is_wp_error( $file_array ) ) {
			return $file_array;
		}

		// do the validation and storage stuff
		$post_id = media_handle_sideload( $file_array, $post_parent, "", $post_data );

		// If error storing permanently, unlink if a tmp file was stored
		if ( $is_url ) {
			static::cleanup_file( $file_array );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( $canonical_id ) {
			static::set_canonical_id( $post_id, $canonical_id );
		}

		return $post_id;
	}

	protected static function require_dependencies() {

		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		require_once( ABSPATH . '/wp-admin/includes/media.php' );
		require_once( ABSPATH . '/wp-admin/includes/image.php' );
	}

	protected static function prepare_file( $path, $is_url ) {

		$file_array = array();

		//Path is a URL
		if ( $is_url ) {

			$file_array['tmp_name'] = \download_url( $path );

			// If error storing temporarily, unlink
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				@unlink( $file_array['tmp_name'] );
				return $file_array['tmp_name'];
			}

			//Path is a file path
		} else {
			$file_array['tmp_name'] = $path;
		}

		// Set variables for storage
		// Fix file filename for query strings
		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png|ico|pdf|csv|txt)/i', $path, $matches );
		$file_array['name']     = basename( $matches[0] );

		return $file_array;
	}

	protected static function cleanup_file( $file_array ) {

		@unlink( $file_array['tmp_name'] );
	}

	static function exists( $canonical_id ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id );
	}

	static function get_id_from_canonical_id( $canonical_id  ) {

		return parent::get_id_from_canonical_id( $canonical_id, 'attachment' );
	}

	static function set_canonical_id( $id, $canonical_id, $post_type = 'post' ) {

		parent::set_canonical_id( $id, $canonical_id, 'attachment' );
	}
}