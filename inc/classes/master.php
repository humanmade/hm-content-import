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
	 * @return bool | Importer\Base
	 */
	public static function get_importer_instance( $key, $args = array() ) {

		$importers = static::get_importers();

		return ! empty( $importers[ $key ] ) ? new $importers[ $key ]( $args ) : false;
	}

	protected static function init_cli() {

		if ( defined( 'WP_CLI' ) && 'WP_CLI' ) {
			\WP_CLI::add_command( 'hmci',  apply_filters( 'hmci_wp_cli_class_name', __NAMESPACE__ . '\\CLI\\Import' ) );
		}
	}
}