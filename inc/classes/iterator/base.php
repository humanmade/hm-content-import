<?php

namespace HMCI\Iterator;

/**
 * Base  iterator class
 * Iterates over provided objects for processing
 *
 * Class Base
 * @package HMCI\Iterator
 */
abstract class Base implements Base_Interface {

	/**
	 * Debug callback
	 *
	 * @var bool
	 */
	var $debugger = false;

	/**
	 * Class instance arguments
	 *
	 * @var array
	 */
	var $args     = array();

	/**
	 * @param array $args
	 * @throws \Exception
	 */
	public function __construct( $args = array() ) {

		$verified = $this->parse_args( $args );

		if ( ! $verified || is_wp_error( $verified ) ) {
			throw new \Exception( ( ! $verified ) ? __( 'Invalid arguments supplied', 'hmci' ) : $verified->get_error_message() );
		}
	}

	/**
	 * Iterate over all items
	 */
	public function iterate_all() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {

			$this->iterate_items( $items );
			$offset += count( $items );

			if ( $this->args['auto_clear_cache'] ) {
				$this->clear_cache();
			}
		}
	}

	/**
	 * Iterate over provided items
	 *
	 * @param $items
	 */
	public function iterate_items( $items ) {

		foreach ( $items as $item ) {

			$r = $this->iterate_item( $item );

			if ( is_wp_error( $r ) ) {
				$this->debug( $r );
			}
		}
	}

	/**
	 * Iterate over a single provided item
	 *
	 * @param $item
	 * @return bool
	 */
	public function iterate_item( $item ) {

		$item   = $this->parse_item( $item );

		if ( ! $item ) {
			return false;
		}

		$id     = $this->process_item( $item );

		if ( is_wp_error( $id ) ) {
			$this->debug( $id );
		}

		return $id;
	}

	/**
	 * Output a debug string
	 *
	 * @param $output
	 */
	protected function debug( $output ) {

		if ( empty( $this->args['verbose'] ) ) {
			return;
		}

		if ( $this->args['debugger'] ) {
			call_user_func( $this->args['debugger'], $output );
		}
	}

	/**
	 * Clear local cache (helps vs memory leaks if object caching is enabled)
	 */
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

	/**
	 * Parse and validate instance arguments
	 *
	 * @param $args
	 * @return bool|\WP_Error
	 */
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
				return new \WP_Error( 'hmci_invalid_arg_type', sprintf( __( 'Invalid argument value. %s has must be of type %s', 'hmci' ), $arg, $data['type'] ) );
			}
		}

		return true;
	}

	/**
	 * Compile instance arguments
	 *
	 * @return array
	 */
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

		return array_merge( $global_args, static::get_iterator_args(), static::get_custom_args() );
	}

	/**
	 * Parse an item being iterated over
	 *
	 * @param $item
	 * @return mixed
	 */
	protected function parse_item( $item ) {
		return $item;
	}

	/**
	 * Get custom arguments of class extension
	 *
	 * @return array
	 */
	public static function get_custom_args() {

		return array();
	}

	/**
	 * Get arguments of iterator type
	 *
	 * @return array
	 */
	public static function get_iterator_args() {

		return array();
	}

	/**
	 * Get number of items to be iterated over
	 *
	 * @return int
	 */
	public function get_count() {

		$offset = 0;

		while ( $items = $this->get_items( $offset, $this->args['items_per_loop'] ) ) {
			$offset += count( $items );
		}

		return $offset;
	}

	/**
	 * Get items (paged)
	 *
	 * @param $offset
	 * @param $count
	 * @return mixed
	 */
	abstract function get_items( $offset, $count );

	/**
	 * Process a single item
	 *
	 * This function is to be defined for specific implementer requirements
	 *
	 * @param $item
	 * @return mixed
	 */
	abstract protected function process_item( $item );
}
