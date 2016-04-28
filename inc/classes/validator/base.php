<?php

namespace HMCI\Validator;

use HMCI\Validator\Interfaces;
use HMCI\Source;

abstract class Base implements Interfaces\Base {

	var $debugger = false;
	var $output   = array();
	var $args     = array();

	public function __construct( $args = array() ) {

		$verified = $this->parse_args( $args );

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

			if ( $r ) {
				$this->output[] = $r;
			}
		}

		$this->output_validation();
	}

	protected function pad_column( $string, $chars = 10 ) {

		while( strlen( $string ) < $chars ) {
			$string .= ' ';
		}

		return $string;
	}

	protected function output_validation() {

		switch( $this->args['output'] ) {

			case 'csv':
				$this->output_csv();
				break;

			default:
				$this->output_debugger();
		}
	}

	protected function output_debugger() {

		foreach( $this->output as $output ) {

			$padded = array_map( array( $this, 'pad_column' ), $output );

			$this->debug( implode( ' - ', $padded ) );
		}
	}

	protected function output_csv() {

		$fp = fopen( $this->args['output_path'] , 'w' );

		if ( ! $fp ) {
			$this->debug( sprintf( 'Could not write to file %s', $this->args['output_path'] ) );
		}

		foreach( $this->output as $output ) {

			fputcsv( $fp, $output );
		}

		fclose( $fp );
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

		return array_merge( static::get_source_args(), static::get_validator_args(), $global_args );
	}

	public static function get_validator_args() {
		return array();
	}

	abstract protected function validate_item( $item );

}
