<?php

namespace HMCI\Importer;

abstract class Base {

	var $args     = array();
	var $debugger = false;

	public function __construct( $args = array() ) {

		$this->args = wp_parse_args( $args, array(
			'items_per_loop'    => 100,
			'debug'             => false,
		) );

		$verified = $this->verify_args();

		if ( ! $verified || is_wp_error( $verified ) ) {
			throw new \Exception( ( ! $verified ) ? __( 'Invalid arguments supplied', 'hmci' ) : $verified->get_message() );
		}
	}

	public function import_all() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {
			$this->import_items( $items );
			$offset += count( $items );
		}
	}

	public function import_items( $items ) {

		foreach ( $items as $item ) {
			$this->import_item( $item );
		}
	}

	public function import_item( $item ) {

		$item   = $this->parse_item( $item );

		if ( ! $item ) {
			return false;
		}

		$id     = $this->insert_item( $item );

		if ( is_wp_error( $id ) ) {
			$this->debug( $id );
		}

		return $id;
	}

	public function get_count() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {
			$offset += count( $items );
		}

		return $offset;
	}

	public function parse_item( $item ) {

		return $item;
	}

	protected function verify_args() {

		return true;
	}

	protected function debug( $output ) {

		if ( empty( $this->args['debug'] ) ) {
			return;
		}

		if ( ! $this->debugger ) {
			$this->debug_default( $output );
		} else {
			call_user_func( $this->debugger, $output );
		}
	}

	protected function debug_default( $output ) {

		var_dump( $output );
	}

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

		if ( is_dir( $path ) ) {

			$files = array_map( function( $item ) use ( $path ) {

				return $path . '/' . $item;

			}, scandir( $path ) );

		} else {

			$files = array( $path );
		}

		return $this->filter_files( $files );
	}

	abstract protected function insert_item( $item );

	abstract public function get_items( $offset, $count );
}
