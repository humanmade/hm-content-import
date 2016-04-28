<?php

// todo:: implement regex replacer with definable regex then callback with received data and expects replacement
// todo:: implement abstraction for replacing attachment urls -> should supply new url to callback along with any type data available, then replace.

namespace HMCI\Importer;

abstract class Post_Content extends Base {

	var $args     = array();
	var $debugger = false;

	protected function insert_item( $item ) {

		$post_content = $this->parse_post_content( $item->post_content, $item );

		wp_update_post( array(
			'ID'            => $item->ID,
			'post_content'  => $post_content,
		) );
	}

	public function get_items( $offset, $count ) {

		$query = $this->get_post_query( array(
			'posts_per_page' => $count,
			'offset'         => $offset,
		) );

		return $query->get_posts();
	}

	protected function get_post_query( $args ) {

		$query = new \WP_Query( wp_parse_args( $args, $this->get_global_query_args() ) );

		return $query;
	}

	protected function get_global_query_args() {

		$whitelist = array(
			'post_type',
			'post_status',
			'post_author',
		);

		$args = array();

		foreach( $whitelist as $accepted_arg ) {

			if ( isset( $this->args[ $accepted_arg ] ) ) {
				$args[ $accepted_arg ] = $this->args[ $accepted_arg ];
			}
		}

		return $args;
	}

	public static function get_arg_definitions() {

		return array(
			'post_type' => array(
				'default'       => 'any',
				'type'          => 'string',
				'description'   => __( 'Post type for post query.', 'hmci' )
			),
			'post_status' => array(
				'type'          => 'string',
				'description'   => __( 'Post status for post query.', 'hmci' )
			),
			'post_author' => array(
				'type'          => 'numeric',
				'description'   => __( 'Post author for post query.', 'hmci' )
			),
		);
	}

	abstract protected function parse_post_content( $post_content, $post );
}
