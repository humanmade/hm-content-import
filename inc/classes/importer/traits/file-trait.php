<?php

namespace HMCI\Importer;

trait File_Trait {

	protected function get_files_in_path() {

		$path        = $this->args['export-path'];
		$check_paths = array( $path, ABSPATH . '/' . $path, ABSPATH . '../' . $path );
		$path_found  = '';

		foreach ( $check_paths as $path ) {

			if ( file_exists( $path ) ) {
				$path_found = $path;
			}
		}

		if ( ! $path_found ) {
			return new \WP_Error( 404, __( 'Path not found', 'hmci' ) );
		}

		if ( is_dir( $path_found ) ) {

			$files = array_map( function( $item ) use ( $path_found ) {

				return $path_found . '/' . $item;

			}, scandir( $path_found ) );

		} else {

			$files = array( $path_found );
		}

		return $files;
	}

	protected function verify_args() {

		$response = parent::verify_args();

		if ( ! $response || is_wp_error( $response ) ) {
			return $response;
		}

		$files = $this->get_files_in_path();

		if ( is_wp_error( $files ) ) {
			return $files;
		}

		return true;
	}
}
