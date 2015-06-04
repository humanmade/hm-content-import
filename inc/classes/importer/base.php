<?php

namespace HMCI\Importer;

abstract class Base {

	var $args     = array();
	var $debugger = false;

	public function __construct( $args = array() ) {

		$this->args = wp_parse_args( $args, array(
			'items_per_loop'    => 100,
			'verbose'           => true,
		) );

		$verified = $this->verify_args();

		if ( ! $verified || is_wp_error( $verified ) ) {
			throw new \Exception( ( ! $verified ) ? __( 'Invalid arguments supplied', 'hmci' ) : $verified->get_error_message() );
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

			var_dump( $items );
			$offset += count( $items );
		}

		var_dump( $offset );

		exit;

		return $offset;
	}

	public function parse_item( $item ) {

		return $item;
	}

	protected function verify_args() {

		return true;
	}

	protected function debug( $output ) {

		if ( empty( $this->args['verbose'] ) ) {
			return;
		}

		if ( $this->debugger ) {
			call_user_func( $this->debugger, $output );
		}
	}

	abstract protected function insert_item( $item );

	abstract public function get_items( $offset, $count );
}
