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

		$this->database_connection = new \wpdb( $this->args['db-user'], $this->args['db-pass'], $this->args['db-name'], $this->args['db-host'] );
	}

	protected function verify_args() {

		$parent = parent::verify_args();

		if ( is_wp_error( $parent ) || ! $parent ) {
			return $parent;
		}

		if ( empty( $this->args['db-user'] ) ) {
			return new \WP_Error( '500', __( 'No db-user arg was specified' ) );
		}

		if ( ! isset( $this->args['db-pass'] ) ) {
			return new \WP_Error( '500', __( 'No db-pass arg was specified' ) );
		}

		if ( empty( $this->args['db-host'] ) ) {
			return new \WP_Error( '500', __( 'No db-host arg was specified' ) );
		}

		if ( empty( $this->args['db-name'] ) ) {
			return new \WP_Error( '500', __('No db-name arg was specified') );
		}

		return true;
	}
}
