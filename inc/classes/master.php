<?php

namespace HMCI;

/**
 * Singleton master class for HMCI
 *
 * Manages
 * - Getting/setting of importer and validator instances
 * - Initialisation of CLI command instances
 *
 * @package HMCI
 */
class Master {

	/**
	 * Master class instance
	 *
	 * @var Master
	 */
	static $instance   = false;


	/**
	 * Registered importer class name array
	 *
	 * @var array
	 */
	static $importers  = array();


	/**
	 * Registered validator class name array
	 *
	 * @var array
	 */
	static $validators = array();

	/**
	 * Get the master class instance
	 *
	 * @return bool
	 */
	public static function get_instance() {

		if ( ! static::$instance ) {
			static::$instance = new static();
			static::$instance->init_cli();
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
			self::$importers[$key] = $class_name;
		}
	}

	/**
	 * Get all importers
	 *
	 * Returns assoc array of importer ID -> class name
	 *
	 * @return array
	 */
	public static function get_importers() {
		return self::$importers;
	}

	/**
	 * Get an importer instance from it's ID
	 *
	 * @param $key
	 * @return bool | \WP_Error | Importer\Base
	 */
	public static function get_importer_instance( $key, $args = array() ) {

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
			self::$validators[$key] = $class_name;
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
	 * @return bool | \WP_Error | Validator\Base
	 */
	public static function get_validator_instance( $key, $args = array() ) {

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
	 * Initialise WP CLI command
	 *
	 */
	protected static function init_cli() {

		if ( defined( 'WP_CLI' ) && 'WP_CLI' ) {
			\WP_CLI::add_command( 'hmci',  apply_filters( 'hmci_wp_cli_class_name', __NAMESPACE__ . '\\CLI\\HMCI' ) );
		}
	}
}