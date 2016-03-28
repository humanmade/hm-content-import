<?php

namespace HMCI\Validator;

abstract class Post extends Base {

	var $args     = array();
	var $debugger = false;

	public function get_items( $offset, $count ) {

		$query = $this->get_post_query( array(
			'posts_per_page' => $count,
			'offset'         => $offset,
		) );

		return $query->get_posts();
	}

	public function get_count() {

		$query = $this->get_post_query( array() );

		return $query->found_posts;
	}

	protected function get_post_query( $args ) {

		$query = new \WP_Query( wp_parse_args( $args, $this->get_query_args() ) );

		return $query;
	}

	protected function get_query_args() {

		$whitelist = array(
			'post_type',
			'post_status',
			'author',
		);

		$passthrough_args = array();

		foreach( $this->args as $key => $arg ) {

			if ( in_array( $key, $whitelist ) ) {
				$passthrough_args[ $key ] = $arg;
			}
		}

		return wp_parse_args( $passthrough_args, array(
			'post_type'      => 'any',
			'post_status'    => 'any',
		) );
	}
}
