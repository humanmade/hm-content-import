<?php

namespace HMCI\Importer;

trait File_Trait {

	protected function get_files_in_path() {

		$path        = $this->args['export_path'];
		$check_paths = array( $path, ABSPATH . '/' . $path, ABSPATH . '../' . $path );
		$path_found  = '';

		foreach ( $check_paths as $path ) {

			if ( file_exists( $path ) ) {
				$path_found = $path;
			}
		}

		if ( ! $path_found ) {
			return new \WP_Error( 'hmci_export_path_not_found', __( sprintf( 'Path not found. Attempted paths: %s', implode( ', ', $check_paths ) ), 'hmci' ) );
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

	protected function parse_args( $args ) {

		$response = parent::parse_args( $args );

		if ( ! $response || is_wp_error( $response ) ) {
			return $response;
		}

		$files = $this->get_files_in_path();

		if ( is_wp_error( $files ) ) {
			return $files;
		}

		return true;
	}

	public static function get_arg_definitions() {

		return array(
			'export_path' => array(
				'required'      => true,
				'type'          => 'string',
				'description'   => __( 'Export path, either absolute path or relative ABSPATH', 'hmci' )
			)
		);
	}
}
