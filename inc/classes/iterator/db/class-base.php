<?php

namespace HMCI\Iterator\DB;

/**
 * Base Database iterator class
 *
 * Iterates over provided database data for processing
 *
 * Class Base
 *
 * @package HMCI\Iterator\DB
 */
abstract class Base extends \HMCI\Iterator\Base {

	/**
	 * @var bool
	 */
	var $database_connection = false;

	/**
	 * @param array $args
	 * @param $type
	 */
	public function __construct( $args = [], $type = 'importer' ) {

		\HMCI\Iterator\Base::__construct( $args, $type );

		$this->set_db();
	}

	/**
	 * Get database class instance
	 *
	 * @return \wpdb|bool
	 */
	public function db() {
		return $this->database_connection;
	}

	/**
	 * Set database class instance
	 */
	public function set_db() {

		$this->database_connection = new \wpdb( $this->args['db_user'], $this->args['db_pass'], $this->args['db_name'], $this->args['db_host'] );
	}

	/**
	 * Get iterator argument definitions
	 *
	 * @return array
	 */
	public static function get_iterator_args() {

		return [
			'db_user' => [
				'default'     => DB_USER,
				'type'        => 'string',
				'description' => __( 'DB username credential for source database.', 'hmci' ),
			],
			'db_pass' => [
				'default'     => DB_PASSWORD,
				'type'        => 'string',
				'description' => __( 'DB password credential for source database.', 'hmci' ),
			],
			'db_name' => [
				'required'    => true,
				'type'        => 'string',
				'description' => __( 'DB name for source database.', 'hmci' ),
			],
			'db_host' => [
				'default'     => DB_HOST,
				'type'        => 'string',
				'description' => __( 'DB host for source database.', 'hmci' ),
			],
		];
	}
}

