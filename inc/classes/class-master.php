<?php

namespace HMCI;

use function HM\Meta_Lookups\register_lookup;

/**
 * Singleton master class for HMCI
 *
 * Manages
 * - Getting/setting of importer and validator instances
 * - Initialization of CLI command instances
 *
 * @package HMCI
 */
class Master {

	/**
	 * Master class instance
	 *
	 * @var Master
	 */
	static $instance = false;

	/**
	 * Registered importer class name array
	 *
	 * @var array
	 */
	static $importers = [];

	/**
	 * Registered validator class name array
	 *
	 * @var array
	 */
	static $validators = [];

	/**
	 * Get the master class instance
	 *
	 * @return static
	 */
	public static function get_instance() {

		if ( ! static::$instance ) {
			static::$instance = new static();
			static::$instance->init_cli();
			static::$instance->init_lookups();
		}

		return static::$instance;
	}

	/**
	 * Add an importer to available importers
	 *
	 * @param $key
	 * @param $class_name
	 */
	public static function add_importer( $key, $class_name ) {

		if ( class_exists( $class_name ) ) {
			self::$importers[ $key ] = $class_name;
		}
	}

	/**
	 * Get all importers
	 *
	 * Returns assoc array of importer ID -> class name
	 *
	 * @return array<string, class-string>
	 */
	public static function get_importers() {
		return self::$importers;
	}

	/**
	 * Get an importer instance from it's ID
	 *
	 * @param $key
	 * @return bool | \WP_Error | Iterator\Base
	 */
	public static function get_importer_instance( $key, $args = [] ) {

		$importers = static::get_importers();

		if ( empty( $importers[ $key ] ) ) {
			return false;
		}

		try {

			$importer = new $importers[ $key ]( $args );

		} catch ( \Exception $e ) {

			return new \WP_Error( 500, $e->getMessage() );
		}

		return $importer;
	}

	/**
	 * Add a validator to available validators
	 *
	 * @param $key
	 * @param $class_name
	 */
	public static function add_validator( $key, $class_name ) {

		if ( class_exists( $class_name ) ) {
			self::$validators[ $key ] = $class_name;
		}
	}

	/**
	 * Get all validator
	 *
	 * Returns assoc array of validator ID -> class name
	 *
	 * @return array
	 */
	public static function get_validators() {
		return self::$validators;
	}

	/**
	 * Get a validator instance from it's ID
	 *
	 * @param $key
	 * @return bool | \WP_Error | Iterator\Base
	 */
	public static function get_validator_instance( $key, $args = [] ) {

		$importers = static::get_validators();

		if ( ! $importers[ $key ] ) {
			return false;
		}

		try {

			$importer = new $importers[ $key ]( $args );

		} catch ( \Exception $e ) {

			return new \WP_Error( 500, $e->getMessage() );
		}

		return $importer;
	}

	/**
	 * Initialize WP CLI command
	 */
	protected function init_cli() {
		if ( defined( 'WP_CLI' ) && 'WP_CLI' ) {
			$class = apply_filters( 'hmci_wp_cli_class_name', __NAMESPACE__ . '\\CLI\\HMCI' );
			$class = new $class();
			$class->register_commands();
		}
	}

	/**
	 * Init canonical ID lookups for all core object types
	 */
	protected function init_lookups() {

		if ( ! is_callable( 'HM\\Meta_Lookups\\register_lookup' ) ) {
			return;
		}

		foreach ( [ 'post', 'comment', 'user', 'term' ] as $type ) {
			register_lookup( sprintf( '%s_%s', CANONICAL_ID_LOOKUP_KEY, $type ), $type, CANONICAL_ID_LOOKUP_KEY );
		}
	}
}
