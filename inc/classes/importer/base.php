<?php

namespace HMCI\Importer;

abstract class Base implements Base_Interface {

	var $debugger = false;
	var $args     = array();

	public function __construct( $args = array() ) {

		$verified = $this->parse_args( $args );

		if ( ! $verified || is_wp_error( $verified ) ) {
			throw new \Exception( ( ! $verified ) ? __( 'Invalid arguments supplied', 'hmci' ) : $verified->get_error_message() );
		}
	}

	public function import_all() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {

			$this->import_items( $items );
			$offset += count( $items );

			if ( $this->args['auto_clear_cache'] ) {
				$this->clear_cache();
			}
		}
	}

	public function import_items( $items ) {

		foreach ( $items as $item ) {

			$r = $this->import_item( $item );

			if ( is_wp_error( $r ) ) {
				$this->debug( $r );
			}
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

	protected function parse_args( $args ) {

		$this->args = $args;

		foreach( $this->get_args() as $arg => $data ) {

			// Arg is not set but has default, define it
			if ( ! isset( $this->args[ $arg ] ) && isset( $data['default'] ) ) {
				$this->args[ $arg ] = $data['default'];
			}

			// Missing required arg
			if ( ! empty( $data['required'] ) && ! array_key_exists( $arg, $this->args ) ) {
				return new \WP_Error( 'hmci_missing_required_arg', sprintf( __( 'Required arg: %s is missing', 'hmci' ), $arg ) );
			}

			// Arg value not in whitelist
			if ( ! empty( $data['values'] ) && is_array( $data['values'] ) && ! in_array( $this->args[ $arg ], $data['values'] ) ) {
				return new \WP_Error( 'hmci_invalid_arg_value', sprintf( __( 'Invalid argument value. %s has the following options: %s', 'hmci' ), $arg, implode( ', ', $data['values'] ) ) );
			}

			// Arg type is numeric but non-numeric value passed
			if ( ! empty( $data['type'] ) && $data['type'] === 'numeric' && ! is_numeric( $this->args[ $arg ] ) ) {
				return new \WP_Error( 'hmci_invalid_arg_value', sprintf( __( 'Invalid argument value. %s has must be of type %s', 'hmci' ), $arg, $data['type'] ) );
			}
		}

		return true;
	}

	public static function get_args() {

		$global_args = array(
			'items_per_loop'    => array(
				'default'       => 100,
				'type'          => 'numeric',
				'description'   => __( 'Number of items to be processed on a single loop, larger are loops are more efficient but more memory intensive.', 'hmci' )
			),
			'verbose'           => array(
				'default'       => true,
				'type'          => 'bool',
				'description'   => __( 'Dictate level of outputting', 'hmci' )
			),
			'auto_clear_cache'  => array(
				'default'       => true,
				'type'          => 'bool',
				'description'   => __( 'Automatically clear local memory cache on each loop - helps prevent memory leak issues', 'hmci' )
			),
		);

		return array_merge( static::get_source_args(), static::get_importer_args(), $global_args );
	}

	public static function get_importer_args() {

		return array();
	}

	abstract protected function insert_item( $item );
}
