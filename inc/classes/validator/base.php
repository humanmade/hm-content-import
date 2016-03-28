<?php

namespace HMCI\Validator;

abstract class Base {

	var $args     = array();
	var $debugger = false;

	public function __construct( $args = array() ) {

		$this->args = wp_parse_args( $args, array(
			'items_per_loop'    => 100,
			'verbose'           => true,
			'auto_clear_cache'  => true,
		) );

		$verified = $this->verify_args();

		if ( ! $verified || is_wp_error( $verified ) ) {
			throw new \Exception( ( ! $verified ) ? __( 'Invalid arguments supplied', 'hmci' ) : $verified->get_error_message() );
		}
	}

	public function validate_all() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {

			$this->validate_items( $items );
			$offset += count( $items );

			if ( $this->args['auto_clear_cache'] ) {
				$this->clear_cache();
			}
		}
	}

	public function validate_items( $items ) {

		foreach ( $items as $item ) {

			$r = $this->validate_item( $item );

			if ( is_wp_error( $r ) ) {
				$this->debug( $r );
			}
		}
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

		if ( empty( $this->args['verbose'] ) ) {
			return;
		}

		if ( $this->args['debugger'] ) {
			call_user_func( $this->args['debugger'], $output );
		}
	}

	protected function clear_cache() {

		global $wpdb, $wp_object_cache;

		$wpdb->queries = array(); // or define( 'WP_IMPORTING', true );

		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}

		$wp_object_cache->group_ops = array();
		//$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();

		if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important
		}

	}

	abstract protected function validate_item( $item );

	abstract public function get_items( $offset, $count );
}
