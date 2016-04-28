<?php

namespace HMCI\Source;

trait Database {

	use Base;

	var $database_connection = false;

	public function __construct( $args = array() ) {

		Base::__construct( $args );

		$this->set_db();
	}

	public function db() {
		return $this->database_connection;
	}

	public function set_db() {

		$this->database_connection = new \wpdb( $this->args['db_user'], $this->args['db_pass'], $this->args['db_name'], $this->args['db_host'] );
	}

	public static function get_arg_definitions() {

		return array(
			'db_user' => array(
				'default'       => DB_USER,
				'type'          => 'string',
				'description'   => __( 'DB username credential for source database.', 'hmci' )
			),
			'db_pass' => array(
				'default'       => DB_PASSWORD,
				'type'          => 'string',
				'description'   => __( 'DB password credential for source database.', 'hmci' )
			),
			'db_name' => array(
				'required'      => true,
				'type'          => 'string',
				'description'   => __( 'DB name for source database.', 'hmci' )
			),
			'db_host' => array(
				'default'       => DB_HOST,
				'type'          => 'string',
				'description'   => __( 'DB host for source database.', 'hmci' )
			)
		);
	}
}
