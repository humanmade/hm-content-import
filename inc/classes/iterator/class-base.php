<?php

namespace HMCI\Iterator;

use function HMCI\Utils\clear_local_object_cache as clear_local_cache;

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
	var $args = [];

	/**
	 * Is the iterator being used as a validator or an importer?
	 *
	 * @var string
	 */
	var $type = 'importer';

	/**
	 * @param array $args
	 * @param $type;
	 *
	 * @throws \Exception
	 */
	public function __construct( $args = [], $type = 'importer' ) {

		$this->type = $type;

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
				clear_local_cache();
			}
		}

		$this->iteration_complete();
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

		$item = $this->parse_item( $item );

		if ( ! $item ) {
			return false;
		}

		$id = $this->process_item( $item );

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
	 * Parse and validate instance arguments
	 *
	 * @param $args
	 * @return bool|\WP_Error
	 */
	protected function parse_args( $args ) {

		$this->args = $args;

		foreach ( $this->get_args() as $arg => $data ) {

			// Arg is not set but has default, define it
			if ( ! isset( $this->args[ $arg ] ) && isset( $data['default'] ) ) {
				$this->args[ $arg ] = $data['default'];
			}

			// Missing required arg
			if ( ! empty( $data['required'] ) && ! array_key_exists( $arg, $this->args ) ) {
				// translators: %s variable refers to the missing arg identifier, i.e. db_name.
				return new \WP_Error( 'hmci_missing_required_arg', sprintf( __( 'Required arg: %s is missing', 'hmci' ), $arg ) );
			}

			// Arg value not in whitelist
			if ( ! empty( $data['values'] ) && is_array( $data['values'] ) && ! in_array( $this->args[ $arg ], $data['values'] ) ) {

				// translators: %1$s refers to the missing arg identifier, i.e. db_name. %2$s refers to a comma separated list of accepted argument values.
				return new \WP_Error( 'hmci_invalid_arg_value', sprintf( __( 'Invalid argument value. %1$s has the following options: %2$s', 'hmci' ), $arg, implode( ', ', $data['values'] ) ) );
			}

			// Arg type is numeric but non-numeric value passed
			if ( array_key_exists( $arg, $this->args ) && ! empty( $data['type'] ) && $data['type'] === 'numeric' && ! is_numeric( $this->args[ $arg ] ) ) {

				// translators: %1$s refers to the missing arg identifier, i.e. db_name. %2$s refers to a variable, type, i.e. 'string'
				return new \WP_Error( 'hmci_invalid_arg_type', sprintf( __( 'Invalid argument value. %1$s has must be of type %2$s', 'hmci' ), $arg, $data['type'] ) );
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
		$global_args = [
			'items_per_loop'   => [
				'default'     => 100,
				'type'        => 'numeric',
				'description' => __( 'Number of items to be processed on a single loop, larger are loops are more efficient but more memory intensive.', 'hmci' ),
			],
			'verbose'          => [
				'default'     => true,
				'type'        => 'bool',
				'description' => __( 'Dictate level of outputting', 'hmci' ),
			],
			'auto_clear_cache' => [
				'default'     => true,
				'type'        => 'bool',
				'description' => __( 'Automatically clear local memory cache on each loop - helps prevent memory leak issues', 'hmci' ),
			],
			'count'            => [
				'default'     => 0,
				'type'        => 'numeric',
				'description' => __( 'Maximum number of items to be imported on execution', 'hmci' ),
			],
			'offset'           => [
				'default'     => 0,
				'type'        => 'numeric',
				'description' => __( 'Offset to begin importing at', 'hmci' ),
			],
			'resume'           => [
				'default'     => false,
				'type'        => 'bool',
				'description' => __( 'Attempt to resume script (if there was a failure during last execution)', 'hmci' ),
			],
		];

		return array_merge( $global_args, static::get_iterator_args(), static::get_custom_args(), static::get_importer_args(), static::get_validator_args() );
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

		return [];
	}

	/**
	 * Get arguments of iterator type
	 *
	 * @return array
	 */
	public static function get_iterator_args() {

		return [];
	}

	/**
	 * Get arguments of iterator when being used for import
	 *
	 * @return array
	 */
	public static function get_importer_args() {

		return [
			'disable_global_terms'        => [
				'default'     => true,
				'type'        => 'bool',
				'description' => __( 'For WPCOMVIP disable global terms. Global terms on VIP installs can cause issues for import', 'hmci' ),
			],
			'disable_trackbacks'          => [
				'default'     => true,
				'type'        => 'bool',
				'description' => __( 'Disable WordPress trackbacks (avoids cron overflow for large imports)', 'hmci' ),
			],
			'disable_intermediate_images' => [
				'default'     => false,
				'type'        => 'bool',
				'description' => __( 'Disables generation of intermediate image sizes during import (preferable for sites using 3rd party image manipulation)', 'hmci' ),
			],
			'define_wp_importing'         => [
				'default'     => true,
				'type'        => 'bool',
				'description' => __( 'Define the WP_IMPORTING flag in WordPress', 'hmci' ),
			],
		];
	}

	/**
	 * Get arguments of iterator when being used for validation
	 *
	 * @return array
	 */
	public static function get_validator_args() {

		return [];
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
	 * Generic callback which can be used when iteration has been completed
	 */
	public function iteration_complete() {

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
