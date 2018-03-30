<?php

namespace HMCI\Inserter\WP;

/**
 *
 * WordPress attachment inserter - manages inserting attachments from url/path, post and meta data
 *
 * @package HMCI\Destination\WP
 */
class Attachment extends Post {

	/**
	 * Upload and add attachment object into the database
	 *
	 *
	 * @param array $post_data    Post data formatted as it will be saved to the posts table. Should match WP_Post data.
	 * @param bool  $canonical_id Use an existing canonical ID.
	 * @param array $post_meta    Metadata to assign to the post.
	 * @param array $options      Additional data about the post.
	 * @return array|int|object|\WP_Error
	 */
	static function insert( $post_data = [], $canonical_id = false, $post_meta = [], $options = [] ) {
		$post_parent = isset( $post_data['post_parent'] ) ? $post_data['post_parent'] : 0;
		$is_url      = filter_var( $options['path'], FILTER_VALIDATE_URL );

		if ( empty( $post_data['ID'] ) && $canonical_id && $current_id = static::get_id_from_canonical_id( $canonical_id ) ) {
			$post_data['ID'] = $current_id;
		}

        if ( ! empty( $post_data['ID'] ) ) {

	        if ( $options['force_update_existing'] === true ) {

		        $post_id = wp_update_post( $post_data, true );

		        if ( $post_meta && is_array( $post_meta ) ) {
			        static::set_meta( $post_id, $post_meta );
		        }

		        static::set_import_path_meta( $post_data['ID'], $options['path'] );
	        }

			return (int) $post_data['ID'];
		}

		static::require_dependencies();

		$file_array = static::prepare_file( $options['path'], $is_url );

		if ( is_wp_error( $file_array ) ) {
			return $file_array;
		}

		// do the validation and storage stuff
		$post_id = media_handle_sideload( $file_array, $post_parent, "", $post_data );

		// If remote file, clean up temp file
		if ( $is_url ) {
			static::cleanup_file( $file_array );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( $canonical_id ) {
			static::set_canonical_id( $post_id, $canonical_id, 'attachment' );
		}

		static::set_import_path_meta( $post_id, $options['path'] );

		if ( $post_meta && is_array( $post_meta ) ) {
			static::set_meta( $post_id, $post_meta );
		}

		return $post_id;
	}

	/**
	 * Ensure files required for managing media uploads are included
	 */
	protected static function require_dependencies() {

		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		require_once( ABSPATH . '/wp-admin/includes/media.php' );
		require_once( ABSPATH . '/wp-admin/includes/image.php' );
	}

	/**
	 * Upload attachment file
	 *
	 * @param $path
	 * @param $is_url
	 * @param null $file_type_override
	 * @return array
	 */
	protected static function prepare_file( $path, $is_url, $file_type_override = null ) {

		$file_array = array();

		//Path is a URL
		if ( $is_url ) {

			try {
				$file_array['tmp_name'] = static::download_url( $path );
			} catch ( \Exception $e ) {
				$file_array['tmp_name'] = new \WP_Error( 500, $e->getMessage() );
			}

			// If error storing temporarily, unlink
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				@unlink( $file_array['tmp_name'] );
				return $file_array['tmp_name'];
			}

		//Path is a file path
		} else {

			$parts = explode( '/', $path );

			$name = end( $parts );

			$tmp_path = sprintf( '/tmp/%s', $name );

			copy( $path, $tmp_path );

			$file_array['tmp_name'] = $tmp_path;
		}

		// Set variables for storage
		// Fix file filename for query strings
		preg_match( apply_filters( 'hmci_attachment_filename_pattern', '/[^\?\/\=]+\.(jpg|jpe|jpeg|gif|png|ico|pdf|csv|txt)/i' ), $path, $matches );

		if ( $file_type_override && empty( $matches ) ) {

			$parts = explode( '/', $path );
			$file_array['name'] = end(  $parts ) . '.' . $file_type_override;

		} if ( empty( $matches ) ) {

			$parts = explode( '/', $path );
            $file_array['name'] = end( $parts ) . '.png';

        } else {

            $file_array['name'] = $matches[0];
        }

		$file_array['name'] = sanitize_file_name( $file_array['name'] );

		return $file_array;
	}

	/**
	 * Remove temporary file
	 *
	 * @param $file_array
	 */
	protected static function cleanup_file( $file_array ) {

		@unlink( $file_array['tmp_name'] );
	}

	/**
	 * Check if attachment exists in the database
	 *
	 * @param mixed  $canonical_id
	 * @param string $post_type
	 * @return bool
	 */
	static function exists( $canonical_id, $post_type = 'attachment' ) {

		return (bool) static::get_id_from_canonical_id( $canonical_id, $post_type );
	}

	/**
	 * Set import path meta
	 *
	 * @param $id
	 * @param $import_path
	 */
	static function set_import_path_meta( $id, $import_path ) {

		update_post_meta( $id, 'hmci_import_path', $import_path );
	}

	/**
	 * Download a remote attachment file
	 *
	 * @param $url
	 * @param int $timeout
	 * @return array|bool|object|string|\WP_Error
	 */
	static function download_url( $url, $timeout = 300 ) {

		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if ( ! $url )
			return new \WP_Error('http_no_url', __('Invalid URL Provided.'));

		/*
		 * Override default functionality from wp download_url function
		 */

		$parts = explode( '/', $url );

		// Set variables for storage
		// Fix file filename for query strings
		$found_extension = strpos( $url, '/' ) !== false && strpos( end( $parts ), '.' ) !== false;

		// wp_tempnam expects an extension to replace but in some cases download urls won't include an extension - it doesn't matter what extension we use
		if ( ! $found_extension ) {
			$tmpfname = wp_tempnam( $url . '.png' );
		} else {
			$tmpfname = wp_tempnam( $url );
		}

		/*
		 * End override default
		 */

		if ( ! $tmpfname )
			return new \WP_Error('http_no_file', __('Could not create Temporary file.'));

		$response = wp_safe_remote_get( $url, array( 'timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname ) );

		if ( is_wp_error( $response ) ) {
			unlink( $tmpfname );
			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ){
			unlink( $tmpfname );
			return new \WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
		}

		$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );
		if ( $content_md5 ) {
			$md5_check = verify_file_md5( $tmpfname, $content_md5 );
			if ( is_wp_error( $md5_check ) ) {
				unlink( $tmpfname );
				return $md5_check;
			}
		}

		return $tmpfname;
	}

}
