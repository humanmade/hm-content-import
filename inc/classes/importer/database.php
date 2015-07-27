<?php

namespace HMCI\Importer;

abstract class Database extends Base {

	var $database_connection = false;

	public function __construct( $args = array() ) {

		parent::__construct( $args );

		$this->set_db();
	}

	public function db() {
		return $this->database_connection;
	}

	public function set_db() {

		$this->database_connection = new \wpdb( $this->args['db_user'], $this->args['db_pass'], $this->args['db_name'], $this->args['db_host'] );

	}

	public function get_items( $offset, $count ) {
		// TODO: Implement get_items() method.
	}

	protected function verify_args() {
		return true;
	}
}
