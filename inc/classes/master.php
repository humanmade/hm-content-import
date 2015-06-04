<?php

namespace HMCI;

class Master {

	static $instance  = false;
	static $importers = array();

	protected function construct() {}

	public static function get_instance() {

		if ( ! static::$instance ) {
			static::$instance = new static();
			static::$instance->init_cli();
		}

		return static::$instance;
	}

	public static function add_importer( $key, $class_name ) {

		if ( class_exists( $class_name ) ) {
			self::$importers[$key] = $class_name;
		}
	}

	public static function get_importers() {
		return self::$importers;
	}

	/**
	 * @param $key
	 * @return bool | WP_Error | Importer\Base
	 */
	public static function get_importer_instance( $key, $args = array() ) {

		$importers = static::get_importers();

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

	protected static function init_cli() {

		if ( defined( 'WP_CLI' ) && 'WP_CLI' ) {
			\WP_CLI::add_command( 'hmci',  apply_filters( 'hmci_wp_cli_class_name', __NAMESPACE__ . '\\CLI\\Import' ) );
		}
	}
}